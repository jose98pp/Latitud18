const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const {JSDOM}=require('jsdom');
const root=path.resolve(__dirname,'../..');
const source=fs.readFileSync(path.join(root,'resources/views/admin/periodico/index.blade.php'),'utf8');
function harness(type='image',zoom=1,attributes={}) {
 const dom=new JSDOM('<div class="id-paper-sheet"></div>',{runScripts:'outside-only',pretendToBeVisual:true});
 const w=dom.window;w.PointerEvent=undefined; // Dispatch the mouse path used by desktop Interact.
 w.eval(fs.readFileSync(path.join(root,'public/vendor/interact/interact.min.js'),'utf8'));
 const frame={id:'f1',type,x:50,y:50,w:200,h:100,content:'Texto editable',titular:'Titular',cuerpo:'Cuerpo',...attributes};
 w.currentEdicion={paginas:[{frames:[frame],ancho:720,alto:1106}]};w.activePageIndex=0;w.zoomLevel=zoom;w.snapGridActive=false;w.snapGridSize=10;
 w.AD_FORMATS={};w.renderLatitudMasthead=()=>'<div>LATITUD 18</div>';
 let history=0,dirty=0;
 w.selectFrame=el=>{el.classList.add('selected');w.selectedFrame=el;};
 w.recordHistory=()=>history++;w.markUnsaved=()=>dirty++;w.updateInspectorGeometry=()=>{};
 for(const name of ['buildFrameElement','initInteract','constrainFrameToPage','syncFrameTextToModel','saveFrameGeoToModel']) {
  const block=source.match(new RegExp('function '+name+'\\([^]*?\\n\\}'));
  assert.ok(block,name);w.eval(block[0]);
 }
 const element=w.buildFrameElement(frame,0);w.document.querySelector('.id-paper-sheet').appendChild(element);
 element.getBoundingClientRect=()=>{const left=parseFloat(element.style.left)*zoom,top=parseFloat(element.style.top)*zoom,width=parseFloat(element.style.width)*zoom,height=parseFloat(element.style.height)*zoom;return {left,top,width,height,right:left+width,bottom:top+height};};
 element.getClientRects=()=>[element.getBoundingClientRect()];
 w.initInteract();
 function mouse(target,event,x,y){target.dispatchEvent(new w.MouseEvent(event,{bubbles:true,cancelable:true,view:w,clientX:x,clientY:y,buttons:event==='mouseup'?0:1,button:0}));}
 function gesture(target,from,to){mouse(target,'mousedown',...from);mouse(w.document,'mousemove',...to);mouse(w.document,'mouseup',...to);}
 return {w,frame,element,mouse,gesture,get history(){return history;},get dirty(){return dirty;},close(){w.interact.stop();dom.window.close();}};
}
test('all editable frame types can be dragged from their visible content',()=>{
 for(const type of ['image','text','headline','article','quote','box','masthead','qr','divider']) {
  const h=harness(type);try {
   h.gesture(h.element.querySelector('img, .frame-text-inner, .frame-quote-inner, .frame-box-inner')||h.element.firstElementChild,[100,100],[130,120]);
   assert.equal(h.frame.x,80,type);assert.equal(h.frame.y,70,type);
   assert.equal(h.history,1);assert.ok(h.dirty>0);
  }finally{h.close();}
 }
});
test('images and text shrink using the resize handle at 50%, 100% and 150% zoom',()=>{
 for(const type of ['image','text'])for(const zoom of [.5,1,1.5]){
  const h=harness(type,zoom);try{
   h.gesture(h.element.querySelector('.resize-handle.se'),[249*zoom,149*zoom],[219*zoom,129*zoom]);
   assert.equal(h.frame.w,170,type+' zoom '+zoom);assert.equal(h.frame.h,80);
   assert.equal(h.frame.x,50);assert.equal(h.frame.y,50);
  }finally{h.close();}
 }
});
test('zoomed dragging writes page coordinates rather than screen coordinates',()=>{
 const h=harness('image',.5);try{
  h.gesture(h.element.querySelector('img'),[50,50],[65,60]);
  assert.equal(h.frame.x,80);assert.equal(h.frame.y,70);
 }finally{h.close();}
});
test('double click edits text without dragging, and input persists to the frame model',()=>{
 const h=harness('text');try{
  const text=h.element.querySelector('.frame-text-inner');
  assert.equal(text.getAttribute('contenteditable'),'false');
  text.dispatchEvent(new h.w.MouseEvent('dblclick',{bubbles:true}));
  assert.equal(text.getAttribute('contenteditable'),'true');
  h.gesture(text,[100,100],[130,120]);assert.equal(h.frame.x,50);
  text.innerHTML='Texto corregido';text.dispatchEvent(new h.w.Event('input',{bubbles:true}));
  assert.equal(h.frame.content,'Texto corregido');
  text.dispatchEvent(new h.w.Event('blur'));assert.equal(text.getAttribute('contenteditable'),'false');
 }finally{h.close();}
});
test('article headline and body remain separate after editing',()=>{
 const h=harness('article');try{
  const texts=h.element.querySelectorAll('.frame-text-inner');texts[0].innerHTML='Nuevo titular';texts[1].innerHTML='Nuevo cuerpo';
  texts[1].dispatchEvent(new h.w.Event('input',{bubbles:true}));
  assert.equal(h.frame.titular,'Nuevo titular');assert.equal(h.frame.cuerpo,'Nuevo cuerpo');assert.equal(h.frame.content,'Nuevo cuerpo');
 }finally{h.close();}
});
test('explicit locks prevent moving and resizing; native image dragging is disabled',()=>{
 const h=harness('image',1,{isLocked:true});try{
  const img=h.element.querySelector('img');assert.equal(img.draggable,false);
  const drag=new h.w.Event('dragstart',{bubbles:true,cancelable:true});img.dispatchEvent(drag);assert.equal(drag.defaultPrevented,true);
  h.gesture(img,[100,100],[130,120]);assert.equal(h.frame.x,50);assert.equal(h.history,0);
 }finally{h.close();}
});
test('blocking mousedown reproduces the original no-movement regression',()=>{
 const h=harness('image');try{
  h.element.addEventListener('mousedown',event=>event.stopPropagation());
  h.gesture(h.element.querySelector('img'),[100,100],[130,120]);assert.equal(h.frame.x,50);assert.equal(h.history,0);
 }finally{h.close();}
});
test('frames added after initialization remain draggable through delegated events',()=>{
 const h=harness('image');try{
  const next={id:'new-text',type:'text',x:50,y:50,w:200,h:100,content:'Nueva caja'};
  h.w.currentEdicion.paginas[0].frames.push(next);
  const el=h.w.buildFrameElement(next,0);h.w.document.querySelector('.id-paper-sheet').appendChild(el);
  el.getBoundingClientRect=()=>({left:50,top:50,right:250,bottom:150,width:200,height:100});el.getClientRects=()=>[el.getBoundingClientRect()];
  h.gesture(el.querySelector('.frame-text-inner'),[100,100],[120,110]);
  assert.equal(next.x,70);assert.equal(next.y,60);
 }finally{h.close();}
});
test('active snap grid aligns dragged frames to snapGridSize intervals',()=>{
 const h=harness('image');try{
  h.w.snapGridActive=true;
  h.gesture(h.element.querySelector('img'),[100,100],[123,117]);
  assert.equal(h.frame.x,70);assert.equal(h.frame.y,70);
 }finally{h.close();}
});

