const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/periodico/reader.js', 'utf8');
function setup({missing = false, mobile = false, reduced = false, absent = false} = {}) {
    const elements = {};
    for (const id of ['readerStage', 'readerEdition', 'readerStatus', 'readerError', 'readerRetry']) {
        elements[id] = {hidden: id === 'readerError', events: {}, dataset: {}, addEventListener(type, fn) {this.events[type] = fn;}, querySelectorAll() {return [];}};
    }
    elements.readerStage.dataset = {pdf: '/documento', vendor: '/vendor/dflip/'};
    let options, sourceUrl, timer, reloaded = false;
    const jq = () => ({flipBook(url, config) {sourceUrl = url; options = config;}});
    jq.fn = {flipBook() {}};
    const DFLIP = {PAGE_MODE: {AUTO: null, SINGLE: 1}, SINGLE_PAGE_MODE: {BOOKLET: 2}};
    const window = {jQuery: missing ? null : jq, DFLIP, location: {reload() {reloaded = true;}}, matchMedia(query) {return {matches: query.includes('reduced') ? reduced : mobile};}, setTimeout(fn) {timer = fn; return 1;}, clearTimeout() {timer = null;}};
    vm.runInNewContext(source, {window, DFLIP, document: {getElementById(id) {return absent && id === 'readerStage' ? null : elements[id];}}});
    return {elements, window, get options() {return options;}, get sourceUrl() {return sourceUrl;}, timeout() {timer();}, get reloaded() {return reloaded;}};
}
test('reads actual PDF with same-origin workers and safe evaluation', () => {
    const app = setup();
    assert.equal(app.sourceUrl, '/documento');
    assert.equal(app.options.docParameters.isEvalSupported, false);
    assert.equal(app.options.pdfjsWorkerSrc, '/vendor/dflip/js/libs/pdf.worker.min.js');
    assert.equal(app.options.pageMode, null);
    assert.equal(app.options.soundEnable, false);
});
test('mobile texture limits and reduced motion', () => {
    const app = setup({mobile: true, reduced: true});
    assert.equal(app.options.maxTextureSize, 2048);
    assert.equal(app.options.pageMode, 1);
    assert.equal(app.options.duration, 1);
});
test('PDF failures offer retry; late success clears error', () => {
    const app = setup(); app.timeout();
    assert.equal(app.elements.readerError.hidden, false);
    app.options.onReady();
    assert.equal(app.elements.readerError.hidden, true);
    assert.equal(app.elements.readerStatus.hidden, true);
});
test('missing library offers fallback and reload', () => {
    const app = setup({missing: true});
    assert.equal(app.elements.readerError.hidden, false);
    app.elements.readerRetry.events.click();
    assert.equal(app.reloaded, true);
});
test('edition selection navigates; empty reader does not initialize engine', () => {
    const app = setup({absent: true});
    app.elements.readerEdition.value = '/periodico/edicion/2';
    app.elements.readerEdition.events.change();
    assert.equal(app.window.location.href, '/periodico/edicion/2');
    assert.equal(app.options, undefined);
});

test('service worker never caches editions or PDF publication permissions', () => {
    const handlers = {};
    vm.runInNewContext(fs.readFileSync('public/sw.js', 'utf8'), {URL, self: {addEventListener(name, fn) {handlers[name] = fn;}}});
    for (const path of ['/periodico', '/periodico/edicion/1', '/periodico/edicion/1/documento', '/periodico/edicion/1/pdf']) {
        let intercepted = false;
        handlers.fetch({request: {method: 'GET', url: 'https://example.com' + path, mode: 'navigate'}, respondWith() {intercepted = true;}});
        assert.equal(intercepted, false);
    }
});
