const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../resources/views/admin/periodico/index.blade.php'),'utf8');
function grid(templates){
 const element={innerHTML:''};
 const ctx={loadedTemplates:templates,EDITION_SLOTS:[{slot:1,seccion:'Portada'},{slot:2,seccion:'Editorial'}],SLOT_DEFAULT_TEMPLATES:{1:'old-1',2:'old-2'},document:{getElementById:()=>element}};
 vm.createContext(ctx);vm.runInContext(source.match(/function renderEditionSlotsGrid\([^]*?\n\}/)[0],ctx);ctx.renderEditionSlotsGrid();return element.innerHTML;
}
test('12 páginas prefiere el catálogo v2 instalado',()=>{
 const t=[{id:'old-1',name:'Anterior',category:'portada'},{id:'tpl-l18-v2-01',name:'Nueva',category:'portada'},{id:'tpl-l18-v2-02',name:'Nueva',category:'editorial'}];
 const html=grid(t);assert.match(html,/<option value="tpl-l18-v2-01" selected>/);assert.match(html,/<option value="tpl-l18-v2-02" selected>/);
});
test('Sin instalar v2 se conserva el catálogo disponible',()=>{
 const html=grid([{id:'old-1',name:'Anterior',category:'portada'}]);assert.match(html,/<option value="old-1" selected>/);
});
test('Los tamaños y colores editados se guardan dentro del contenido del marco',()=>{
 const helper=fs.readFileSync(path.join(__dirname,'../../resources/views/admin/periodico/editor-tools.blade.php'),'utf8');
 const child={style:{}};const text={style:{},innerHTML:'contenido',querySelectorAll:()=>[child]};const frame={id:'titulo'};
 const ctx={selectedFrame:{dataset:{frameId:'titulo'},querySelector:()=>text},currentEdicion:{paginas:[{frames:[frame]}]},activePageIndex:0,recordHistory(){},markUnsaved(){}};
 vm.createContext(ctx);vm.runInContext(helper,ctx);ctx.persistFrameTextStyles({fontSize:'28px',color:'#D71920'});
 assert.equal(child.style.fontSize,'28px');assert.equal(child.style.color,'#D71920');assert.equal(frame.content,'contenido');assert.equal(frame.text_style.fontSize,'28px');assert.equal(frame.text_style.color,'#D71920');
 assert.equal(child.style.lineHeight,'1.18');
});
