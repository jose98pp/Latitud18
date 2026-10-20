const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs'), vm = require('node:vm'), path = require('node:path');
const editor = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/periodico/index.blade.php'), 'utf8');
const tools = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/periodico/editor-tools.blade.php'), 'utf8');
function setup() {
    const article = {id:'same-id', type:'article', titular:'Anterior', cuerpo:'Anterior'};
    const nodes = [{dataset:{articleField:'titular'},innerHTML:'<b>Titular editado</b>'}, {dataset:{articleField:'cuerpo'},innerHTML:'<p>Cuerpo editado</p>'}];
    const element = {dataset:{frameId:article.id},querySelectorAll:()=>nodes};
    const indicator = {style:{}};
    const ctx = {currentEdicion:{id:'edition',paginas:[{id:'temporary',frames:[article]}]},
        hasUnsavedChanges:true,editorRevision:1,saveInFlight:null,requests:[],messages:[],navigated:[],
        document:{querySelector:()=>({querySelectorAll:()=>[element]}),querySelectorAll:()=>[],getElementById:()=>indicator},
        showToast(message,type){ctx.messages.push({message,type});},window:{location:{assign:url=>ctx.navigated.push(url)}}};
    ctx.fetch = async (url, options) => {
        ctx.requests.push(JSON.parse(options.body));
        return {ok:true,json:async()=>({success:true,edicion:{pdf_url:null,paginas:[{id:45}]}})};
    };
    vm.createContext(ctx); vm.runInContext(tools,ctx);
    for (const name of ['saveEdicionLayout','ensureEditionSaved','downloadEditionPdf']) vm.runInContext(editor.match(new RegExp('async function '+name+'\\([^]*?\\n\\}'))[0],ctx);
    return {ctx,article,nodes,indicator};
}
test('Guardar persiste por separado titular y cuerpo y adopta el ID real de la página',async()=>{
    const {ctx}=setup(); assert.equal(await ctx.saveEdicionLayout(),true);
    assert.equal(ctx.requests[0].paginas[0].frames[0].titular,'<b>Titular editado</b>');
    assert.equal(ctx.requests[0].paginas[0].frames[0].cuerpo,'<p>Cuerpo editado</p>');
    assert.equal(ctx.currentEdicion.paginas[0].id,45); assert.equal(ctx.hasUnsavedChanges,false);
});
test('Una respuesta 422 no se presenta como guardada ni descarga un PDF anterior',async()=>{
    const {ctx}=setup(); ctx.fetch=async()=>({ok:false,json:async()=>({success:false,message:'Imagen requerida'})});
    await ctx.downloadEditionPdf('/edition.pdf');
    assert.equal(ctx.hasUnsavedChanges,true); assert.equal(ctx.navigated.length,0);
    assert.equal(ctx.messages[0].type,'error'); assert.match(ctx.messages[0].message,/Imagen requerida/);
});
test('PDF espera al guardado y conserva los cambios escritos durante la petición',async()=>{
    const {ctx,nodes}=setup(); let resolve;
    const original=ctx.fetch; ctx.fetch=async(...args)=>{const response=await original(...args); if(ctx.requests.length===1) await new Promise(r=>resolve=r); return response;};
    const download=ctx.downloadEditionPdf('/edition.pdf'); await new Promise(r=>setImmediate(r));
    assert.equal(ctx.navigated.length,0);
    nodes[1].innerHTML='<p>Modificado mientras guardaba</p>'; ctx.editorRevision++; ctx.hasUnsavedChanges=true; resolve();
    await download;
    assert.equal(ctx.requests.length,2);
    assert.equal(ctx.requests[1].paginas[0].frames[0].cuerpo,'<p>Modificado mientras guardaba</p>');
    assert.deepEqual(ctx.navigated,['/edition.pdf']);
});
test('Marcos con el mismo ID en páginas diferentes conservan sus propios contenidos',()=>{
    const {ctx}=setup(); ctx.currentEdicion.paginas=[0,1].map(()=>({frames:[{id:'repeated',type:'text'}]}));
    ctx.document.querySelector=selector=>({querySelectorAll:()=>[{dataset:{frameId:'repeated'},querySelector:()=>({innerHTML:selector.includes('"0"')?'Primera':'Segunda'})}]});
    ctx.syncEditorText(); assert.equal(ctx.currentEdicion.paginas[0].frames[0].content,'Primera'); assert.equal(ctx.currentEdicion.paginas[1].frames[0].content,'Segunda');
});
test('Una edición con PDF subido y sin cambios puede descargarse sin páginas de editor',async()=>{
    const {ctx}=setup(); ctx.currentEdicion.paginas=[]; ctx.hasUnsavedChanges=false;
    ctx.fetch=()=>{throw new Error('No debe intentar guardar una maqueta vacía');};
    await ctx.downloadEditionPdf('/uploaded.pdf'); assert.deepEqual(ctx.navigated,['/uploaded.pdf']);
});
test('Solicitudes de guardado simultáneas se serializan y conservan la revisión más reciente',async()=>{
    const {ctx,nodes}=setup(); let release; let active=0,maxActive=0; const original=ctx.fetch;
    ctx.fetch=async(...args)=>{
        active++; maxActive=Math.max(maxActive,active); const response=await original(...args);
        if(ctx.requests.length===1) await new Promise(r=>release=r);
        active--; return response;
    };
    const first=ctx.saveEdicionLayout(); const second=ctx.saveEdicionLayout(); const third=ctx.saveEdicionLayout();
    await new Promise(r=>setImmediate(r)); nodes[1].innerHTML='Última corrección';ctx.editorRevision++;release();
    await Promise.all([first,second,third]);
    assert.equal(maxActive,1); assert.equal(ctx.requests.length,2); assert.equal(ctx.hasUnsavedChanges,false);
    assert.equal(ctx.requests[1].paginas[0].frames[0].cuerpo,'Última corrección');
});
test('La edición directa del texto actualiza la página correcta cuando se repite un ID de marco',()=>{
    const {ctx}=setup();
    ctx.currentEdicion.paginas=[0,1].map(()=>({frames:[{id:'repeated',type:'article',titular:'Anterior',cuerpo:'Anterior'}]}));
    vm.runInContext(editor.match(/function syncFrameTextToModel\([^]*?\n\}/)[0],ctx);
    const element={dataset:{frameId:'repeated'},closest:()=>({dataset:{page:'1'}}),querySelectorAll:()=>[{innerHTML:'Titular segunda página'},{innerHTML:'Cuerpo segunda página'}]};
    ctx.syncFrameTextToModel(element);
    assert.equal(ctx.currentEdicion.paginas[0].frames[0].cuerpo,'Anterior');
    assert.equal(ctx.currentEdicion.paginas[1].frames[0].titular,'Titular segunda página');
    assert.equal(ctx.currentEdicion.paginas[1].frames[0].cuerpo,'Cuerpo segunda página');
});
