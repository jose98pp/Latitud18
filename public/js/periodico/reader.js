/* PDF-backed reader: retain the exact dimensions and typography of the edition. */
(function () {
    'use strict';
    const stage = document.getElementById('readerStage');
    const selector = document.getElementById('readerEdition');
    if (selector) selector.addEventListener('change', () => { window.location.href = selector.value; });
    if (!stage) return;
    const status = document.getElementById('readerStatus');
    const error = document.getElementById('readerError');
    let ready = false;
    const fail = () => {
        if (ready) return;
        status.hidden = true;
        error.hidden = false;
    };
    document.getElementById('readerRetry').addEventListener('click', () => window.location.reload());
    if (!window.jQuery || !window.DFLIP || !window.jQuery.fn.flipBook) { fail(); return; }
    const vendor = stage.dataset.vendor;
    const watchdog = window.setTimeout(fail, 90000);
    try {
        window.jQuery('#readerBook').flipBook(stage.dataset.pdf, {
            height: '100%',
            webgl: true,
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
            docParameters: { url: stage.dataset.pdf, isEvalSupported: false },
            text: {
                toggleThumbnails: 'Ver miniaturas', previousPage: 'Página anterior', nextPage: 'Página siguiente',
                toggleFullscreen: 'Pantalla completa', zoomIn: 'Ampliar', zoomOut: 'Reducir',
                singlePageMode: 'Una página', doublePageMode: 'Dos páginas',
                gotoFirstPage: 'Ir a portada', gotoLastPage: 'Última página', loading: 'Cargando',
                toggleHelp: 'Ayuda', toggleOutline: 'Índice', downloadPDFFile: 'Descargar PDF'
            },
            onReady: function () {
                ready = true;
                window.clearTimeout(watchdog);
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
    } catch (exception) { window.clearTimeout(watchdog); fail(); }
}());
