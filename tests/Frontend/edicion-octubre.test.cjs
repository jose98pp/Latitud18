const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../resources/views/admin/periodico/index.blade.php'),'utf8');
test('El selector extendido ofrece las 28 páginas con el suplemento de avisos',()=>{
 const grid={innerHTML:''};
 const ctx={document:{getElementById:id=>id==='editionPageCount'?{value:'28'}:grid},EDITION_SLOTS:Array.from({length:12},(_,i)=>({slot:i+1,seccion:'Sección '+(i+1)})),loadedTemplates:Array.from({length:28},(_,i)=>({id:'tpl-l18-oct-28-'+String(i+1).padStart(2,'0'),name:'Página',category:'avisos'})),SLOT_DEFAULT_TEMPLATES:{}};
 vm.createContext(ctx);vm.runInContext(source.match(/function renderEditionSlotsGrid\([^]*?\n\}/)[0],ctx);ctx.renderEditionSlotsGrid();
 assert.equal((grid.innerHTML.match(/class="id-form-input edition-slot-select"/g)||[]).length,28);
 assert.match(grid.innerHTML,/<option value="tpl-l18-oct-28-28" selected>/);
 assert.match(grid.innerHTML,/Avisos \/ Edictos/);
});
