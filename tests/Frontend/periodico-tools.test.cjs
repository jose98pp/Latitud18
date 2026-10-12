const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const code = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/periodico/editor-tools.blade.php'), 'utf8');
function context(pages) {
    const ctx = {currentEdicion:{paginas:pages},activePageIndex:0,frameIdCounter:0,
        confirm:()=>true,prompt:()=> 'Página renombrada',showToast(){},deselectAll(){},renderAllPages(){},renderPageThumbs(){},
        switchPage(){},markUnsaved(){},history:[],recordHistory(){this.history.push(JSON.stringify(this.currentEdicion));}};
    // Los callbacks globales se invocan sin receptor.
    ctx.recordHistory=()=>ctx.history.push(JSON.stringify(ctx.currentEdicion));
    vm.createContext(ctx);vm.runInContext(code,ctx);return ctx;
}
const pages = () => [1,2,3].map(i => ({id:i,numero:i,nombre:'Página '+i,frames:[{id:'f'+i,type:'text',content:'Texto '+i}]}));

test('Eliminar renumera, conserva los IDs restantes y permite restaurar desde el historial',()=>{
    const c=context(pages());c.activePageIndex=2;c.deleteEditorPage(1);
    assert.deepEqual(Array.from(c.currentEdicion.paginas,p=>p.id),[1,3]);
    assert.deepEqual(Array.from(c.currentEdicion.paginas,p=>p.numero),[1,2]);
    assert.equal(c.activePageIndex,1);assert.equal(c.currentEdicion.num_paginas,2);
    assert.equal(JSON.parse(c.history[0]).paginas.length,3);
});
test('Cancelar la eliminación y conservar la última página no modifica la edición',()=>{
    const c=context(pages());c.confirm=()=>false;c.deleteEditorPage(1);assert.equal(c.currentEdicion.paginas.length,3);
    c.currentEdicion.paginas=[c.currentEdicion.paginas[0]];c.confirm=()=>true;c.deleteEditorPage(0);assert.equal(c.currentEdicion.paginas.length,1);
});
test('Duplicar crea IDs independientes y no comparte contenido con la página original',()=>{
    const c=context(pages());c.duplicateEditorPage(0);
    const [first,copy]=c.currentEdicion.paginas;assert.notEqual(copy.id,first.id);assert.notEqual(copy.frames[0].id,first.frames[0].id);
    copy.frames[0].content='Cambio';assert.equal(first.frames[0].content,'Texto 1');assert.equal(c.activePageIndex,1);
});
test('Mover conserva la página seleccionada y renombrar persiste el nombre',()=>{
    const c=context(pages());c.moveEditorPage(0,1);assert.equal(c.currentEdicion.paginas[1].id,1);assert.equal(c.activePageIndex,1);
    c.renameEditorPage(1);assert.equal(c.currentEdicion.paginas[1].nombre,'Página renombrada');
});
test('La cabecera nueva utiliza la marca Latitud18 y acepta campos vacíos',()=>{
    const c=context([]);assert.equal(c.mastheadValues({}).newspaperName,'LATITUD');assert.equal(c.mastheadValues({}).subBadge,'18');
    const f={newspaperName:'LATITUD',subBadge:'18',motto:'',editionDate:'12/10/2026',editionNumber:'N.º 4',city:'Santa Cruz',price:'Bs 5',website:'ejemplo.bo'};
    const html=c.renderLatitudMasthead(f,{});for(const value of ['LATITUD','18','12/10/2026','N.º 4','Santa Cruz','Bs 5','ejemplo.bo'])assert.ok(html.includes(value));
    assert.ok(!html.includes('INFORMACIÓN SIN RUIDO'));
});
test('Los textos y logotipos de cabecera no ejecutan HTML ni URLs de JavaScript',()=>{
    const c=context([]);const html=c.renderLatitudMasthead({newspaperName:'<script>alert(1)</script>',logo_src:'javascript:alert(1)'},{});
    assert.ok(html.includes('&lt;script&gt;'));assert.ok(!html.includes('<script>'));assert.ok(!html.includes('javascript:'));
});
test('Escape cierra una ventana antes del panel de noticias',()=>{
    const c=context([]);let closed;let removed=false;
    c.closeModal=id=>{closed=id;};c.document={querySelectorAll:()=>[{id:'plantillas'}],getElementById:()=>({classList:{contains:()=>true,remove:()=>{removed=true;}}})};
    assert.equal(c.closeEditorOverlay(),true);assert.equal(closed,'plantillas');assert.equal(removed,false);
    c.document.querySelectorAll=()=>[];assert.equal(c.closeEditorOverlay(),true);assert.equal(removed,true);
});

test('El zoom se inicializa, respeta los límites y puede restablecerse',()=>{
    const c=context([]);const display={};const wrapper={style:{}};
    c.document={getElementById:id=>id==='zoomDisplay'?display:wrapper};c.zoomLevel=1;
    c.updateZoomDisplay();assert.equal(display.textContent,'100%');
    c.changeZoom(4);assert.equal(c.zoomLevel,2);assert.equal(display.textContent,'200%');
    c.changeZoom(-4);assert.equal(c.zoomLevel,.25);assert.equal(display.textContent,'25%');
    c.resetZoom();assert.equal(c.zoomLevel,1);assert.equal(wrapper.style.zoom,1);
});
