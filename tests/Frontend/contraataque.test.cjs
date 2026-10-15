const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../public/js/contraataque-scoreboard.js'),'utf8');
function harness(){
 const calls=[],timers=new Map(),listeners={};let id=0;
 const element=()=>({dataset:{},innerHTML:'<span class="ca-badge-live">VIVO</span>',textContent:'',setAttribute(){},prepend(node){this.notice=node.textContent;},querySelectorAll(){return [{remove:()=>{this.innerHTML='sin badge';}}];}});
 const els={caMatchesContainer:element(),caStandingsContainer:element(),caTickerContainer:element(),caStandingsLeague:element(),caMatchDate:{value:'2026-10-15',validity:{valid:true},addEventListener(n,f){listeners[n]=f;}}};
 els.caMatchesContainer.dataset={endpoint:'/contraataque/api/partidos',refresh:'180'};
 const tabs=['bolivia','premier'].map(key=>({dataset:{league:key},textContent:key,classList:{toggle(){}}}));
 const document={hidden:false,getElementById:id=>els[id],querySelector:()=>tabs[0],querySelectorAll:()=>tabs,createElement:()=>({setAttribute(){}}),addEventListener(n,f){listeners[n]=f;}};
 const ctx={document,window:{location:{origin:'https://example.test'}},URL,AbortController,setTimeout(f,ms){timers.set(++id,{f,ms});return id;},clearTimeout(id){timers.delete(id);},fetch(url,options){return new Promise((resolve,reject)=>calls.push({url,options,resolve,reject}));}};
 vm.createContext(ctx);vm.runInContext(source,ctx);
 return {ctx,els,tabs,calls,timers,listeners,document};
}
const tick=()=>new Promise(resolve=>setImmediate(resolve));
const response=(league,label)=>({ok:true,json:async()=>({liga:league,liga_meta:{name:label},matches_html:label,standings_html:'Tabla '+label,ticker_html:'Tira '+label})});
test('Una respuesta atrasada no sustituye la liga elegida ni su tabla o tira de resultados',async()=>{
 const h=harness();h.ctx.window.switchLeagueScoreboard('premier',h.tabs[1]);h.ctx.window.switchLeagueScoreboard('bolivia',h.tabs[0]);
 assert.equal(h.calls[0].options.signal.aborted,true);
 h.calls[1].resolve(response('bolivia','Bolivia'));await tick();h.calls[0].resolve(response('premier','Inglaterra'));await tick();
 assert.equal(h.els.caMatchesContainer.innerHTML,'Bolivia');assert.equal(h.els.caStandingsContainer.innerHTML,'Tabla Bolivia');assert.equal(h.els.caTickerContainer.innerHTML,'Tira Bolivia');
});
test('Una falla al refrescar retira las marcas de directo y avisa que los datos son anteriores',async()=>{
 const h=harness();const timer=[...h.timers.values()].find(t=>t.ms===180000);timer.f();h.calls[0].reject(new Error('Sin conexión'));await tick();
 for(const id of ['caMatchesContainer','caStandingsContainer','caTickerContainer']){assert.match(h.els[id].notice,/última consulta/);assert.equal(h.els[id].innerHTML,'sin badge');}
});
test('No consulta ligas desconocidas ni actualiza en una pestaña oculta',()=>{
 const h=harness();h.ctx.window.switchLeagueScoreboard('desconocida');assert.equal(h.calls.length,0);
 h.document.hidden=true;h.listeners.visibilitychange();h.ctx.window.switchLeagueScoreboard('premier',h.tabs[1]);assert.equal(h.calls.length,0);
 h.document.hidden=false;h.listeners.visibilitychange();assert.equal(h.calls.length,1);
});
