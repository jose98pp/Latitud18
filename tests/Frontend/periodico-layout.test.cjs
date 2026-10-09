const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/periodico/index.blade.php'), 'utf8');
const template = JSON.parse(fs.readFileSync(path.join(__dirname, '../../database/data/latitud18/01-pagina.latitud-template'), 'utf8'));
const clone = value => JSON.parse(JSON.stringify(value));
function load(name, context) {
  const fn = source.match(new RegExp('function ' + name + '\\([^]*?\\n\\}'));
  assert.ok(fn, name + ' debe existir en el editor');
  vm.createContext(context);
  vm.runInContext(fn[0], context);
}

test('Arrastrar o redimensionar mantiene los marcos dentro de los márgenes', () => {
  const ctx = {};
  load('constrainFrameToPage', ctx);
  for (const x of [-100, 0, 200, 800]) for (const y of [-100, 0, 400, 1200]) for (const w of [80, 500, 1000]) {
    const el = { style: { left:x+'px', top:y+'px', width:w+'px', height:'1300px' } };
    ctx.constrainFrameToPage(el, { ancho:720, alto:1106, configuracion:template.configuracion });
    const result = Object.fromEntries(Object.entries(el.style).map(([k,v]) => [k, parseFloat(v)]));
    assert.ok(result.left >= 31 && result.top >= 31);
    assert.ok(result.left + result.width <= 689 && result.top + result.height <= 1075);
  }
});

test('El límite nuevo conserva la geometría de páginas anteriores', () => {
  const ctx = {};
  load('constrainFrameToPage', ctx);
  const el = {style:{left:'0px',top:'0px',width:'900px',height:'50px'}};
  ctx.constrainFrameToPage(el, {ancho:720,alto:1040});
  assert.equal(el.style.width, '900px');
});

test('Aplicar desde el catálogo copia medidas y configuración sin modificar el original', () => {
  const sheet = {style:{}, querySelectorAll:() => [], querySelector:() => ({style:{}}), appendChild() {}};
  const page = {ancho:720,alto:1040,frames:[]};
  const before = JSON.stringify(template);
  const ctx = { loadedTemplates:[template], currentEdicion:{paginas:[page]}, activePageIndex:0, frameIdCounter:0,
    document:{querySelector:() => sheet}, closeModal(){}, recordHistory(){}, buildFrameElement:() => ({}),
    deselectAll(){}, markUnsaved(){}, showToast(){}, confirm:() => true };
  load('applyTemplateFromCatalog', ctx);
  ctx.applyTemplateFromCatalog(template.id);
  assert.equal(page.alto, 1106);
  assert.equal(page.configuracion.page_width_mm, 280);
  assert.equal(sheet.style.height, '1106px');
  assert.equal(page.frames.length, template.frames.length);
  page.frames[0].content = 'Texto nuevo';
  assert.equal(JSON.stringify(template), before);
});

test('El ajuste de imagen se guarda en el modelo para sobrevivir a la recarga', () => {
  const img = {style:{}};
  const frame = {id:'foto',type:'image'};
  const ctx = {selectedFrame:{dataset:{frameId:'foto'},querySelector:() => img}, document:{getElementById:() => ({value:'contain'})},
    currentEdicion:{paginas:[{frames:[frame]}]}, activePageIndex:0, markUnsaved(){}};
  load('applyImageFit',ctx);
  ctx.applyImageFit();
  assert.equal(img.style.objectFit,'contain');
  assert.equal(JSON.parse(JSON.stringify(frame)).image_fit,'contain');
});

test('Exportar una página incluye la configuración física de la plantilla', () => {
  let result;
  const ctx = {currentEdicion:{titulo:'Prueba',numero_edicion:'1',paginas:[{frames:clone(template.frames),configuracion:clone(template.configuracion)}]},
    activePageIndex:0, downloadJsonAsFile:data => {result=data;}, showToast(){}};
  load('exportCurrentAsTemplateFile',ctx);
  ctx.exportCurrentAsTemplateFile();
  assert.equal(result.template.configuracion.page_width_mm,280);
  assert.equal(result.template.configuracion.page_height_px,1106);
});

test('La previsualización usa la altura real de la página y el ajuste de imagen guardado', () => {
  const container = {style:{},children:[],appendChild(el){this.children.push(el);}};
  const frame = {id:'foto',type:'image',x:31,y:31,w:200,h:300,src:'foto.jpg',image_fit:'contain'};
  const ctx = {currentEdicion:{paginas:[{ancho:720,alto:1106,frames:[frame]}]},previewActivePage:0,
    document:{getElementById:id => id==='previewSheetWrapper' ? container : {value:0},createElement:() => ({style:{}})}};
  load('previewRenderPage',ctx);
  ctx.previewRenderPage(0);
  assert.equal(container.style.height,'1106px');
  assert.match(container.children[0].innerHTML,/object-fit:contain/);
});
