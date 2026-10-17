const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/periodico/reader.js', 'utf8');
const tick = () => new Promise(resolve => setImmediate(resolve));
function setup({missing = false, mobile = false, absent = false, http = 200, html = false, libs = true, delay = false, pending = false} = {}) {
    const elements = {}, timers = new Map(), calls = [], scripts = [], readyQueue = [];
    let id = 0, disposed = 0;
    for (const key of ['readerStage', 'readerEdition', 'readerStatus', 'readerError', 'readerRetry', 'reader2D', 'readerBook', 'readerErrorMessage', 'readerPdfFallback']) {
        elements[key] = {hidden: key === 'readerError' || key === 'readerPdfFallback', events: {}, dataset: {}, addEventListener(type, fn) {this.events[type] = fn;}, querySelectorAll() {return [];}};
    }
    elements.readerStage.dataset = {pdf: '/periodico/edicion/1/documento', vendor: '/vendor/dflip/'};
    const DFLIP = {defaults: {pdfjsSrc: 'js/libs/pdf.min.js'}, PAGE_MODE: {AUTO: null, SINGLE: 1}, SINGLE_PAGE_MODE: {BOOKLET: 2}};
    const jq = arg => {
        if (typeof arg === 'function') { if (delay) readyQueue.push(arg); else arg(); return; }
        return {flipBook(url, options) {calls.push({url, options}); return {dispose() {disposed++;}};}};
    };
    jq.fn = {flipBook() {}};
    const window = {jQuery: missing ? null : jq, DFLIP, location: {href: 'https://example.com/periodico'}, addEventListener() {},
        pdfjsLib: libs ? {} : null, THREE: libs ? {} : null, MOCKUP: libs ? {} : null,
        fetch: async (url, options) => {window.request = {url, options}; if (pending) return new Promise((resolve, reject) => options.signal.addEventListener('abort', () => reject(Object.assign(new Error('Aborted'), {name: 'AbortError'})))); return {ok: http === 200, status: http, arrayBuffer: async () => new TextEncoder().encode(html ? '<html>Error</html>' : '%PDF-1.4 test').buffer};},
        matchMedia() {return {matches: mobile};}, setTimeout(fn, ms) {timers.set(++id, {fn, ms}); return id;}, clearTimeout(key) {timers.delete(key);}};
    const document = {getElementById(key) {return absent && key === 'readerStage' ? null : elements[key];}, createElement() {return {};}, head: {appendChild(node) {scripts.push(node);}}};
    class TestURL extends URL {static createObjectURL() {return 'blob:test';} static revokeObjectURL() {}}
    vm.runInNewContext(source, {window, DFLIP, document, URL: TestURL, Blob, Uint8Array, TextDecoder, AbortController});
    return {elements, window, calls, scripts, timers, readyQueue, get disposed() {return disposed;}, timeout(ms) {const timer = [...timers.values()].find(t => t.ms === ms);assert.ok(timer);timer.fn();}};
}
test('configures global dependency paths before initializing and fetches PDF once', async () => {
    const app = setup(); await tick();
    assert.equal(app.window.DFLIP.defaults.pdfjsSrc, 'https://example.com/vendor/dflip/js/libs/pdf.min.js');
    assert.equal(app.window.DFLIP.defaults.pdfjsWorkerSrc, 'https://example.com/vendor/dflip/js/libs/pdf.worker.min.js');
    assert.equal(app.calls.length, 1);
    assert.equal(app.calls[0].options.docParameters.isEvalSupported, false);
    assert.equal(new TextDecoder().decode(app.calls[0].options.docParameters.data), '%PDF-1.4 test');
    assert.equal(app.calls[0].options.docParameters.url, undefined);
});
test('waits for library ready handler before setting defaults', async () => {
    const app = setup({delay: true}); await tick();
    assert.equal(app.calls.length, 0);
    app.window.DFLIP.defaults.pdfjsSrc = '/incorrect-autodetected-path';
    app.readyQueue.shift()(); await tick();
    assert.equal(app.calls.length, 1);
    assert.match(app.window.DFLIP.defaults.pdfjsSrc, /^https:\/\/example.com\/vendor\//);
});
test('HTTP errors stop spinner immediately and show response status', async () => {
    const app = setup({http: 503}); await tick();
    assert.equal(app.calls.length, 0);
    assert.equal(app.elements.readerBook.hidden, true);
    assert.equal(app.elements.readerError.hidden, false);
    assert.match(app.elements.readerErrorMessage.textContent, /503/);
});
test('HTML response never reaches PDF engine', async () => {
    const app = setup({html: true}); await tick();
    assert.equal(app.calls.length, 0);
    assert.match(app.elements.readerErrorMessage.textContent, /PDF válido/);
});
test('failed script exposes PDF fallback without an infinite spinner', async () => {
    const app = setup({libs: false}); await tick();
    assert.equal(app.scripts[0].src, 'https://example.com/vendor/dflip/js/libs/pdf.min.js');
    app.scripts[0].onerror(); await tick();
    assert.equal(app.elements.readerPdfFallback.src, 'blob:test');
    assert.equal(app.elements.readerBook.hidden, true);
});
test('render timeout disposes engine and late callbacks cannot hide fallback', async () => {
    const app = setup(); await tick();
    app.timeout(25000);
    assert.equal(app.disposed, 1);
    assert.equal(app.elements.readerPdfFallback.hidden, false);
    app.calls[0].options.onReady();
    assert.equal(app.elements.readerError.hidden, false);
});
test('2D retry reuses valid PDF and restores successful viewer', async () => {
    const app = setup(); await tick(); app.timeout(25000);
    app.elements.reader2D.events.click(); await tick();
    assert.equal(app.calls[1].options.webgl, false);
    app.calls[1].options.onReady();
    assert.equal(app.elements.readerStatus.hidden, true);
    assert.equal(app.elements.readerError.hidden, true);
    assert.equal(app.elements.readerPdfFallback.hidden, true);
});
test('missing library and empty edition are handled without unhandled errors', async () => {
    const app = setup({missing: true}); await tick();
    assert.equal(app.elements.readerError.hidden, false);
    assert.equal(setup({absent: true}).calls.length, 0);
});
test('slow request is aborted on timeout', async () => {
    const app = setup({pending: true}); await tick();
    assert.equal(app.window.request.options.signal.aborted, false);
    app.timeout(60000); await tick();
    assert.equal(app.window.request.options.signal.aborted, true);
    assert.equal(app.elements.readerError.hidden, false);
    assert.equal(app.calls.length, 0);
});
test('mobile uses one page and disables animation for reduced motion', async () => {
    const app = setup({mobile: true}); await tick();
    assert.equal(app.calls[0].options.pageMode, 1);
    assert.equal(app.calls[0].options.duration, 1);
});
test('service worker never caches editions or publication permissions', () => {
    const handlers = {};
    vm.runInNewContext(fs.readFileSync('public/sw.js', 'utf8'), {URL, self: {addEventListener(name, fn) {handlers[name] = fn;}}});
    for (const path of ['/periodico', '/periodico/edicion/1/documento', '/periodico/edicion/1/pdf']) {
        let intercepted = false;
        handlers.fetch({request: {method: 'GET', url: 'https://example.com' + path, mode: 'navigate'}, respondWith() {intercepted = true;}});
        assert.equal(intercepted, false);
    }
});
