/* PDF-backed reader: retain the exact dimensions and typography of the edition. */
(function () {
    'use strict';
    const stage = document.getElementById('readerStage');
    const selector = document.getElementById('readerEdition');
    if (selector) selector.addEventListener('change', () => { window.location.href = selector.value; });
    if (!stage) return;
    const status = document.getElementById('readerStatus');
    const error = document.getElementById('readerError');
    const book = document.getElementById('readerBook');
    const fallback = document.getElementById('readerPdfFallback');
    const message = document.getElementById('readerErrorMessage');
    let app = null, generation = 0, timer = null, pdfBytes = null, pdfUrl = null, activeRequest = null;
    let ready = false;
    const stop = () => {
        window.clearTimeout(timer);
        if (activeRequest) activeRequest.abort();
        activeRequest = null;
        if (app && typeof app.dispose === 'function') {
            try { app.dispose(); } catch (exception) { /* A partially built engine may not dispose. */ }
        }
        app = null;
        book.innerHTML = '';
        book.hidden = true;
    };
    const fail = (text, token) => {
        if (token !== generation || ready) return;
        generation++; // Ignore callbacks from the cancelled viewer.
        stop();
        status.hidden = true;
        error.hidden = false;
        message.textContent = text;
        // A valid PDF remains readable even when WebGL or a library fails.
        if (pdfBytes && fallback) {
            if (!pdfUrl) pdfUrl = URL.createObjectURL(new Blob([pdfBytes], {type: 'application/pdf'}));
            fallback.src = pdfUrl;
            fallback.hidden = false;
            stage.dataset.state = 'fallback';
        }
    };
    const loadScript = (src, available) => available() ? Promise.resolve() : new Promise((resolve, reject) => {
        const script = document.createElement('script');
        const timeout = window.setTimeout(() => reject(new Error('No se pudo cargar una herramienta del lector.')), 15000);
        script.src = src;
        script.onload = () => {
            window.clearTimeout(timeout);
            if (available()) resolve(); else reject(new Error('Una herramienta del lector no está disponible.'));
        };
        script.onerror = () => { window.clearTimeout(timeout); reject(new Error('No se pudo descargar una herramienta del lector.')); };
        document.head.appendChild(script);
    });
    async function start(webgl = true) {
        const token = ++generation;
        ready = false;
        stop();
        status.hidden = false;
        error.hidden = true;
        if (fallback) fallback.hidden = true;
        stage.dataset.state = 'loading';
        const abort = activeRequest = new AbortController();
        timer = window.setTimeout(() => { abort.abort(); fail('La edición tardó demasiado en responder. Puedes reintentar o abrir el PDF.', token); }, 60000);
        try {
            if (!window.jQuery || !window.DFLIP || !window.jQuery.fn.flipBook) throw new Error('No se pudo cargar el motor del lector.');
            // DearFlip 1.7 loads dependencies from DFLIP.defaults, not per-book options.
            // Wait until its own ready handler finishes auto-detecting its location.
            await new Promise(resolve => window.jQuery(resolve));
            if (token !== generation) return;
            const vendor = new URL(stage.dataset.vendor, window.location.href).href;
            Object.assign(window.DFLIP.defaults, {
                pdfjsSrc: vendor + 'js/libs/pdf.min.js',
                pdfjsWorkerSrc: vendor + 'js/libs/pdf.worker.min.js',
                pdfjsCompatibilitySrc: vendor + 'js/libs/compatibility.js',
                threejsSrc: vendor + 'js/libs/three.min.js',
                mockupjsSrc: vendor + 'js/libs/mockup.min.js',
                cMapUrl: vendor + 'js/libs/cmaps/'
            });
            if (!pdfBytes) {
                status.textContent = 'Preparando el PDF de la edición…';
                const response = await window.fetch(stage.dataset.pdf, {credentials: 'same-origin', cache: 'no-store', signal: abort.signal, headers: {Accept: 'application/pdf'}});
                if (!response.ok) throw new Error('No se pudo obtener la edición (HTTP ' + response.status + ').');
                const bytes = await response.arrayBuffer();
                const signature = new TextDecoder('ascii').decode(new Uint8Array(bytes, 0, Math.min(bytes.byteLength, 1024)));
                if (!signature.includes('%PDF-')) throw new Error('El servidor no devolvió un PDF válido.');
                if (token !== generation) return;
                pdfBytes = bytes;
            }
            status.textContent = 'Abriendo las páginas…';
            await loadScript(DFLIP.defaults.pdfjsSrc, () => !!window.pdfjsLib);
            if (webgl) {
                await loadScript(DFLIP.defaults.threejsSrc, () => !!window.THREE);
                await loadScript(DFLIP.defaults.mockupjsSrc, () => !!window.MOCKUP);
            }
            if (token !== generation) return;
            window.clearTimeout(timer);
            timer = window.setTimeout(() => fail('El lector no pudo dibujar las páginas. Prueba el modo 2D o lee el PDF aquí debajo.', token), 25000);
            book.hidden = false;
            app = window.jQuery('#readerBook').flipBook(stage.dataset.pdf, {
            height: '100%',
            webgl: webgl,
            backgroundColor: 'transparent',
            transparent: true,
            soundEnable: false,
            autoPlay: false,
            duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 1 : 700,
            maxTextureSize: window.matchMedia('(max-width: 640px)').matches ? 2048 : 3072,
            pageMode: window.matchMedia('(max-width: 760px)').matches ? DFLIP.PAGE_MODE.SINGLE : DFLIP.PAGE_MODE.AUTO,
            singlePageMode: DFLIP.SINGLE_PAGE_MODE.BOOKLET,
            paddingTop: 16, paddingBottom: 48, paddingLeft: 12, paddingRight: 12,
            allControls: 'altPrev,pageNumber,altNext,thumbnail,zoomIn,zoomOut,fullScreen,more,pageMode,startPage,endPage',
            moreControls: 'pageMode,startPage,endPage',
            hideControls: 'sound,play,share,search,outline,download',
            pdfjsSrc: vendor + 'js/libs/pdf.min.js',
            pdfjsWorkerSrc: vendor + 'js/libs/pdf.worker.min.js',
            pdfjsCompatibilitySrc: vendor + 'js/libs/compatibility.js',
            threejsSrc: vendor + 'js/libs/three.min.js',
            mockupjsSrc: vendor + 'js/libs/mockup.min.js',
            cMapUrl: vendor + 'js/libs/cmaps/',
            // Disable PDF.js dynamic code evaluation even for an uploaded PDF.
            docParameters: { data: new Uint8Array(pdfBytes.slice(0)), isEvalSupported: false, cMapUrl: vendor + 'js/libs/cmaps/', cMapPacked: true },
            text: {
                toggleThumbnails: 'Ver miniaturas', previousPage: 'Página anterior', nextPage: 'Página siguiente',
                toggleFullscreen: 'Pantalla completa', zoomIn: 'Ampliar', zoomOut: 'Reducir',
                singlePageMode: 'Una página', doublePageMode: 'Dos páginas',
                gotoFirstPage: 'Ir a portada', gotoLastPage: 'Última página', loading: 'Cargando',
                toggleHelp: 'Ayuda', toggleOutline: 'Índice', downloadPDFFile: 'Descargar PDF'
            },
            onReady: function () {
                if (token !== generation) return;
                ready = true;
                stage.dataset.state = 'ready';
                window.clearTimeout(timer);
                status.hidden = true;
                error.hidden = true;
                // The bundled engine exposes toolbar actions as divs; add keyboard access.
                stage.querySelectorAll('.df-ui-btn').forEach(button => {
                    if (button.querySelector('input')) return;
                    button.setAttribute('role', 'button');
                    button.setAttribute('tabindex', '0');
                    const label = button.getAttribute('title') || button.textContent.trim();
                    if (label) button.setAttribute('aria-label', label);
                    button.addEventListener('keydown', event => {
                        if (event.target !== button) return;
                        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); button.click(); }
                    });
                });
            }
        });
        } catch (exception) {
            fail(exception.name === 'AbortError' ? 'La carga tardó demasiado. Vuelve a intentarlo.' : exception.message, token);
        }
    }
    document.getElementById('readerRetry').addEventListener('click', () => start(true));
    document.getElementById('reader2D').addEventListener('click', () => start(false));
    window.addEventListener('pagehide', () => {
        generation++;
        stop();
        if (pdfUrl) URL.revokeObjectURL(pdfUrl);
    });
    start();
}());
