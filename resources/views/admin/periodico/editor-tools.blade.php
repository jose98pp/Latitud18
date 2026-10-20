// Funciones compartidas por el editor, el inspector y la previsualización.
function escapeEditorial(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function mastheadValues(frame = {}, edition = {}) {
    return {
        newspaperName: frame.newspaperName ?? 'LATITUD', subBadge: frame.subBadge ?? '18',
        motto: frame.motto ?? 'INFORMACIÓN SIN RUIDO',
        editionNumber: frame.numero_edicion ?? frame.editionNumber ?? edition.numero_edicion ?? '',
        editionDate: frame.fecha ?? frame.editionDate ?? edition.fecha ?? '',
        city: frame.ciudad ?? frame.city ?? edition.ciudad ?? 'Santa Cruz de la Sierra',
        price: frame.price ?? edition.precio ?? 'Bs 7,00', website: frame.website ?? 'latitud18.com',
        leftEar: frame.leftEar ?? '', rightEar: frame.rightEar ?? '', logo_src: frame.logo_src ?? '',
        nameColor: /^#[0-9a-f]{6}$/i.test(frame.nameColor || '') ? frame.nameColor : '#0B1F3A',
        accentColor: /^#[0-9a-f]{6}$/i.test(frame.accentColor || '') ? frame.accentColor : '#D71920',
        nameSize: Math.max(16, Math.min(80, Number(frame.nameSize) || 44)),
        logoWidth: Math.max(30, Math.min(200, Number(frame.logoWidth) || 80))
    };
}

function renderLatitudMasthead(frame, edition) {
    const f = mastheadValues(frame, edition);
    const e = escapeEditorial;
    const logo = /^(https?:\/\/|\/(?!\/))/i.test(f.logo_src) ? `<img alt="Logotipo" src="${e(f.logo_src)}" style="width:${f.logoWidth}px;max-height:65px;object-fit:contain;vertical-align:middle;"> ` : '';
    return `<div style="width:100%;height:100%;box-sizing:border-box;overflow:hidden;border-bottom:3px solid ${f.accentColor};font-family:Montserrat,Arial,sans-serif;color:${f.nameColor};">
        <div style="font-size:10px;display:flex;justify-content:space-between;"><span>${e(f.leftEar)}</span><span>${e(f.rightEar)}</span></div>
        <div style="font-size:${f.nameSize}px;font-weight:800;line-height:1.2;white-space:nowrap;">${logo}${e(f.newspaperName)} <span style="background:${f.accentColor};color:#fff;padding:0 6px;">${e(f.subBadge)}</span></div>
        <div style="font-size:11px;letter-spacing:1px;line-height:1.3;">${e(f.motto)}</div>
        <div style="font-size:10px;margin-top:5px;border-top:1px solid #cbd5e1;padding-top:3px;">${e(f.editionNumber)} · ${e(f.editionDate)} · ${e(f.city)} · ${e(f.price)}</div>
        <div style="font-size:9px;margin-top:3px;">${e(f.website)}</div>
    </div>`;
}

function renumberEditorPages() {
    currentEdicion.paginas.forEach((page, i) => { page.numero = i + 1; if (/^Página \d+/.test(page.nombre || '')) page.nombre = page.nombre.replace(/^Página \d+/, 'Página ' + (i + 1)); });
    currentEdicion.num_paginas = currentEdicion.paginas.length;
    activePageIndex = Math.max(0, Math.min(activePageIndex, currentEdicion.paginas.length - 1));
}

function deleteEditorPage(index) {
    const pages = currentEdicion.paginas;
    if (!pages[index]) return;
    if (pages.length === 1) { showToast('La edición debe conservar al menos una página.', 'info'); return; }
    if (!confirm(`¿Eliminar la página ${index + 1}? Se quitarán sus noticias y publicidad. Puedes deshacer antes de guardar.`)) return;
    recordHistory();
    pages.splice(index, 1);
    if (index < activePageIndex) activePageIndex--;
    renumberEditorPages(); deselectAll(); renderAllPages(); switchPage(activePageIndex); markUnsaved();
}

function duplicateEditorPage(index) {
    if (!currentEdicion.paginas[index]) return;
    recordHistory();
    const copy = JSON.parse(JSON.stringify(currentEdicion.paginas[index]));
    copy.id = 'pg-' + Date.now() + '-' + (++frameIdCounter);
    copy.nombre = (copy.nombre || 'Página') + ' (Copia)';
    (copy.frames || []).forEach(f => { f.id = 'f-copy-' + Date.now() + '-' + (++frameIdCounter); });
    currentEdicion.paginas.splice(index + 1, 0, copy);
    activePageIndex = index + 1; renumberEditorPages(); renderAllPages(); switchPage(activePageIndex); markUnsaved();
}

function moveEditorPage(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= currentEdicion.paginas.length) return;
    recordHistory();
    const active = currentEdicion.paginas[activePageIndex];
    const page = currentEdicion.paginas.splice(index, 1)[0];
    currentEdicion.paginas.splice(target, 0, page);
    activePageIndex = currentEdicion.paginas.indexOf(active);
    renumberEditorPages(); renderAllPages(); switchPage(activePageIndex); markUnsaved();
}

function renameEditorPage(index) {
    const page = currentEdicion.paginas[index];
    if (!page) return;
    const name = prompt('Nombre de la página:', page.nombre || `Página ${index + 1}`);
    if (!name?.trim()) return;
    recordHistory(); page.nombre = name.trim().slice(0, 150); renderPageThumbs(); markUnsaved();
}

function resetLatitudMasthead() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'masthead') return;
    const frame = currentEdicion.paginas[activePageIndex].frames.find(f => f.id === selectedFrame.dataset.frameId);
    if (!frame) return;
    recordHistory();
    Object.assign(frame, {newspaperName:'LATITUD', subBadge:'18', motto:'INFORMACIÓN SIN RUIDO', nameColor:'#0B1F3A', accentColor:'#D71920', leftEar:'', rightEar:''});
    selectedFrame.innerHTML = renderLatitudMasthead(frame, currentEdicion);
    populateInspector(selectedFrame); markUnsaved();
}

function closeEditorOverlay() {
    const overlays = [...document.querySelectorAll('#editorWorkspace .id-modal-overlay.open')];
    if (overlays.length) { closeModal(overlays[overlays.length - 1].id); return true; }
    const drawer = document.getElementById('newsDrawer');
    if (drawer?.classList.contains('open')) { drawer.classList.remove('open'); return true; }
    return false;
}

function syncEditorText() {
    (currentEdicion.paginas || []).forEach((page, index) => {
      const sheet = document.querySelector(`.id-paper-sheet[data-page="${index}"]`);
      const elements = new Map([...(sheet?.querySelectorAll('.id-frame') || [])].map(element => [element.dataset.frameId, element]));
      (page.frames || []).forEach(frame => {
        const element = elements.get(String(frame.id));
        if (frame.type === 'article') {
            element?.querySelectorAll('[data-article-field]').forEach(node => {
                frame[node.dataset.articleField] = node.innerHTML.trim();
            });
        } else {
            const text = element?.querySelector('.frame-text-inner, .frame-quote-inner, .frame-box-inner');
            if (text) frame.content = text.innerHTML;
        }
      });
    });
}

function updateZoomDisplay() {
    const display = document.getElementById('zoomDisplay');
    const wrapper = document.getElementById('paperWrapper');
    if (display) display.textContent = Math.round(zoomLevel * 100) + '%';
    if (wrapper) wrapper.style.zoom = zoomLevel;
}

function changeZoom(delta) {
    zoomLevel = Math.round(Math.max(0.25, Math.min(2, zoomLevel + delta)) * 100) / 100;
    updateZoomDisplay();
}

function resetZoom() {
    zoomLevel = 1;
    updateZoomDisplay();
}

function persistFrameTextStyles(styles) {
    if (!selectedFrame) return;
    const text = selectedFrame.querySelector('.frame-text-inner');
    if (!text) return;
    styles = {...styles};
    if ('fontSize' in styles && !('lineHeight' in styles)) styles.lineHeight = '1.18';
    recordHistory();
    Object.assign(text.style, styles);
    const inlineStyles = {...styles};
    delete inlineStyles.columnCount;
    delete inlineStyles.columnGap;
    text.querySelectorAll('p, h1, h2, h3, h4, h5, h6').forEach(node => Object.assign(node.style, inlineStyles));
    const frame = currentEdicion.paginas[activePageIndex]?.frames?.find(f => f.id == selectedFrame.dataset.frameId);
    if (frame) {
        const styleKey = frame.type === 'article' ? 'article_title_style' : 'text_style';
        frame[styleKey] = {...frame[styleKey], ...inlineStyles};
        if (frame.type === 'article') frame.titular = text.innerHTML.trim();
        else frame.content = text.innerHTML;
        if ('columnCount' in styles) frame.columns = Number(styles.columnCount) || 1;
    }
    markUnsaved();
}

function editorialColorHex(color) {
    if (color === 'transparent' || /rgba\([^)]*,\s*0(?:\.0+)?\s*\)/.test(color || '')) return '#ffffff';
    if (/^#[0-9a-f]{6}$/i.test(color || '')) return color;
    const parts = String(color).match(/\d+/g);
    return parts?.length >= 3 ? '#' + parts.slice(0, 3).map(v => Number(v).toString(16).padStart(2, '0')).join('') : '#ffffff';
}
