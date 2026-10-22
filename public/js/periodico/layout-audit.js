/* Measures the actual editor layout without changing or truncating the saved content. */
(function () {
    'use strict';
    const textSelector = '.frame-text-inner, .frame-quote-inner, .frame-box-inner';
    function audit() {
        const issues = [];
        document.querySelectorAll('#sheetsContainer > .id-paper-sheet').forEach(sheet => {
            // Hidden pages need a layout too. A detached clone avoids observer loops and flicker.
            const clone = sheet.cloneNode(true);
            clone.removeAttribute('id');
            clone.style.cssText += ';display:block!important;position:fixed!important;left:-100000px!important;top:0!important;visibility:hidden!important;transform:none!important;';
            document.body.appendChild(clone);
            try {
                const originals = sheet.querySelectorAll('.id-frame');
                clone.querySelectorAll('.id-frame').forEach((frame, index) => {
                    const width = parseFloat(sheet.style.width), height = parseFloat(sheet.style.height);
                    const outside = frame.offsetLeft < -1 || frame.offsetTop < -1 || frame.offsetLeft + frame.offsetWidth > width + 1 || frame.offsetTop + frame.offsetHeight > height + 1;
                    const clipped = Array.from(frame.querySelectorAll(textSelector)).some(text => text.scrollHeight > text.clientHeight + 2 || text.scrollWidth > text.clientWidth + 2);
                    const issue = outside || clipped;
                    originals[index]?.classList.toggle('id-frame-overflow', issue);
                    if (issue) issues.push({page: Number(sheet.dataset.page) + 1, frame: originals[index]?.id, reason: outside ? 'Elemento fuera de la página' : 'Texto que no cabe en su marco'});
                });
            } finally { clone.remove(); }
        });
        const summary = document.getElementById('layoutAuditSummary');
        if (summary) {
            summary.textContent = issues.length ? `${issues.length} aviso(s) de maquetación en página(s) ${[...new Set(issues.map(i => i.page))].join(', ')}. Revisa los marcos naranjas: amplía el marco, reduce la letra o reparte el texto antes de exportar. El PDF puede recortar contenido.` : 'No se detecta texto desbordado ni elementos fuera de página. Revisa también el PDF antes de publicar.';
            summary.dataset.warning = issues.length ? 'true' : 'false';
        }
        return issues;
    }
    window.PeriodicoLayoutAudit = {audit};
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('sheetsContainer');
        if (!root) return;
        let timer;
        const schedule = () => { clearTimeout(timer); timer = setTimeout(audit, 350); };
        new MutationObserver(schedule).observe(root, {subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['style']});
        window.addEventListener('resize', schedule);
        if (document.fonts) document.fonts.ready.then(schedule);
        schedule();
    });
})();
