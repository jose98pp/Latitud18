"""Rebuild the editorial reference with independent frames, not page screenshots."""
import json,html,pathlib,hashlib
R=pathlib.Path(__file__).resolve().parents[1]; OUT=R/'database/data/latitud18-v2'
W,H,M,U,G,C=720,1106,31,658,10,123.6
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
pdfmetrics.registerFont(TTFont('LayoutMontserrat',str(R/'public/fonts/Montserrat-Regular.ttf')))
pdfmetrics.registerFont(TTFont('LayoutBebas',str(R/'public/fonts/BebasNeue-Regular.ttf')))
NAVY,RED,BLACK='#0B1F3A','#D71920','#111111'
SECTIONS=['Portada','Editorial / Opinión','Política','Política','Santa Cruz','Santa Cruz','País','País','Economía','Economía','Seguridad / Judicial','Mundo / Deportes / Cultura']
SAMPLE='Este espacio contiene texto de muestra para comprobar la lectura y la distribución de la página. Sustituya estas líneas por la información de su edición. La noticia debe presentar antecedentes, datos comprobados y declaraciones de sus protagonistas. El cierre puede incorporar contexto y explicar los próximos pasos. '
CFG={'columns':5,'column_width_px':C,'gutter_px':G,'margin_px':M,'page_width_px':W,'page_height_px':H,'page_width_mm':280,'page_height_mm':430,'margin_mm':12,'gutter_mm':4,'schema_version':2,'brand':'latitud18','edition_family':'editorial-v2'}
class Page:
 def __init__(self,n):self.n=n;self.f=[];self.section=SECTIONS[n-1]
 def frame(self,t,x,y,w,h,**kw):
  self.f.append(dict(id=f'l18v2-p{self.n}-{len(self.f)+1}',type=t,x=round(x),y=round(y),w=round(w),h=round(h),z=len(self.f)+1,isLocked=False,**kw))
 def text(self,x,y,w,h,text,size=10,bold=False,color=BLACK,bg=None,font='Montserrat',align='left',role='text'):
  style=f"font-family:'{font}';font-size:{size}px;font-weight:{700 if bold and font!='Bebas Neue' else 400};line-height:{size*1.18:.2f}px;margin:0;color:{color};text-align:{align};"
  if bg:style+=f'background:{bg};padding:4px;'
  self.frame('text',x,y,w,h,content=f'<p style="{style}">{html.escape(text)}</p>',editorial_role=role,example_content=True)
 def title(self,x,y,w,h,text,size=34):
  while size>12:
   line='';lines=1
   for word in text.split():
    candidate=(line+' '+word).strip()
    if pdfmetrics.stringWidth(candidate,'LayoutBebas',size)>w:lines+=1;line=word
    else:line=candidate
   if lines*size*1.18+size*.65+4<=h:break
   size-=.5
  self.text(x,y,w,h,text,size,False,NAVY,font='Bebas Neue',role='headline')
 def body(self,x,y,w,h,count=1):
  cw=(w-G*(count-1))/count
  for j in range(count):
   # Aim for a filled but editable column, leaving approximately one baseline at the foot.
   max_lines=max(2,int(h/(9.3*1.55))-2); words=[]; line=''; lines=1
   for word in (SAMPLE*30).split():
    candidate=(line+' '+word).strip()
    if pdfmetrics.stringWidth(candidate,'LayoutMontserrat',9.3)>cw:
     if lines>=max_lines:break
     lines+=1;line=word
    else:line=candidate
    words.append(word)
   s=' '.join(words).rstrip(' ,;:')+'.'
   self.text(x+j*(cw+G),y,cw,h,s,9.3,role='body')
 def rule(self,x,y,w,color=RED):self.frame('divider',x,y,w,2,color=color)
 def photo(self,x,y,w,h,label='FOTOGRAFÍA PRINCIPAL',portrait=False):
  self.frame('image',x,y,w,h,src='/images/periodico/latitud18-editorial-photo.svg',caption='',alt=label,image_fit='cover',editorial_role='photo',replacement_label=label)
 def caption(self,x,y,w,text='Pie de fotografía editable · Crédito: autor'):self.text(x,y,w,17,text,7.8,color='#475569',role='caption')
 def ad(self,y,h=100):self.frame('ad',M,y,U,h,format_code='F1',status='disponible',lockResize=False,advertiser_image_url='',image_fit='contain',editorial_role='advertisement')
 def header(self):
  self.text(M,31,388,15,'LATITUD 18 · INFORMACIÓN SIN RUIDO',9,True,NAVY,role='folio')
  self.text(433,31,256,15,f'FECHA __ / __ / ____ · PÁG. {self.n}',8,color=NAVY,align='right',role='folio')
  self.rule(M,52,U)
  self.text(M,63,U,29,self.section.upper(),20,True,NAVY,role='section')
  self.rule(M,102,U)
 def footer(self):self.rule(M,1053,U,NAVY);self.text(M,1061,U,14,f'LATITUD18 · INFORMACIÓN SIN RUIDO                                    PÁGINA {self.n}',8,color=NAVY,role='folio')
 def brief(self,x,y,w,h,title,photo=False):
  self.title(x,y,w,45,title,21)
  if photo:self.photo(x,y+51,w,90,'FOTO SECUNDARIA');self.caption(x,y+143,w);self.body(x,y+165,w,h-165)
  else:self.body(x,y+52,w,h-52)
 def data(self,x,y,w,h,label):self.text(x,y,w,h,label,20,True,NAVY,bg='#edf1f6',align='center',role='data-box')
 def pack(self):return {'id':f'tpl-l18-v2-{self.n:02}','name':f'Latitud18 v2 · {self.n:02} · {self.section}','category':['portada','editorial','politica','politica','santacruz','santacruz','pais','pais','economia','economia','seguridad','mundo'][self.n-1],'description':'Composición editorial diferenciada. Columnas, fotos, pies y noticias independientes. Referencia local + identidad Latitud18.','preview_color':NAVY,'configuracion':dict(CFG,section_label=self.section),'frames':self.f}
P=[]
for n in range(1,13):
 p=Page(n);P.append(p)
 if n!=1:p.header()
 if n==1:
  p.text(M,31,U,16,'EDICIÓN N.º __ · SEMANA DEL __ AL __',9,role='folio')
  p.text(M,54,525,96,'LATITUD',77,True,NAVY,role='brand');p.text(568,66,121,84,'18',48,True,'#ffffff',RED,font='Montserrat',align='center',role='brand')
  p.text(M,154,U,25,'INFORMACIÓN SIN RUIDO',16,True,NAVY,role='brand')
  p.rule(M,189,U,NAVY);p.text(M,198,U,17,'SANTA CRUZ DE LA SIERRA · BOLIVIA · PRECIO Bs __ · 12 PÁGINAS',8,role='folio')
  p.photo(M,227,U,470);p.text(45,476,118,23,'POLÍTICA',11,True,'#fff',RED,role='kicker')
  p.text(45,509,630,130,'ASAMBLEA Y DEBATE: LOS TEMAS CLAVE DE LA EDICIÓN',37,True,'#fff',NAVY,font='Bebas Neue',role='headline')
  p.text(45,648,630,37,'Resumen de la noticia principal y referencia a su desarrollo en páginas interiores.',12,color='#fff',bg=NAVY,role='deck')
  for j,sec in enumerate(['ECONOMÍA','SANTA CRUZ','SEGURIDAD']):
   x=M+j*223;p.rule(x,710,212);p.text(x,719,212,18,sec,10,True,RED,role='kicker');p.title(x,745,212,53,['Actividad económica y perspectivas','Proyectos y prioridades de la ciudad','Investigaciones y actualidad'][j],22);p.photo(x,809,212,90,'FOTO SECUNDARIA');p.text(x,901,212,14,'LEER EN PÁGINA __',8,True)
  p.ad(918,123)
 elif n==2:
  p.text(M,117,388,18,'EDITORIAL',10,True,RED,role='kicker');p.title(M,143,388,82,'Información clara para una ciudadanía mejor informada',31)
  p.body(M,242,388,484,3);p.photo(M,744,388,220,'FOTO EDITORIAL');p.caption(M,970,388)
  p.text(433,117,256,24,'OPINIÓN Y COLUMNISTAS',13,True,RED,role='kicker')
  for j in range(3):
   y=155+j*255;p.photo(433,y,60,72,'RETRATO');p.text(505,y,184,30,'Nombre del columnista',10,True);p.title(505,y+34,184,64,['Una mirada a la semana','Ideas para la ciudad','El valor de la participación'][j],21);p.body(433,y+106,256,129,2);p.rule(433,y+242,256,'#cbd5e1')
  p.text(433,936,256,66,'REDACCIÓN LATITUD18\nContacto y créditos editables',10,True,NAVY,bg='#edf1f6')
 elif n==3:
  p.title(M,119,U,88,'Agenda legislativa: las claves del debate político',35);p.text(M,214,U,40,'Bajada editable para resumir la propuesta, sus antecedentes y las posiciones de los protagonistas.',11,role='deck')
  p.body(M,271,254,360,2);p.photo(299,271,390,337);p.caption(299,613,390)
  p.rule(M,646,U)
  for j,title in enumerate(['La propuesta','Las posiciones','Los próximos pasos']):x=M+j*223;p.text(x,660,212,28,str(j+1),24,True,RED);p.title(x,701,212,44,title,22);p.body(x,757,212,273)
 elif n==4:
  p.title(M,119,388,87,'Gobierno y oposición: una semana de decisiones',33);p.text(433,126,256,76,'CONTEXTO\nClaves y antecedentes del debate',12,True,NAVY,bg='#edf1f6',role='data-box')
  p.photo(M,223,388,278);p.caption(M,507,388);p.body(433,223,256,391,2);p.body(M,533,388,82,3)
  p.rule(M,628,U)
  p.brief(M,646,324,384,'Reformas: alcances y efectos',True);p.brief(365,646,324,384,'La agenda de los actores políticos',True)
 elif n==5:
  p.title(M,120,U,85,'Santa Cruz: inversiones y proyectos para su desarrollo',35);p.photo(M,223,U,338);p.caption(M,567,U)
  p.rule(M,593,U);p.brief(M,611,324,416,'Obras y servicios para los barrios',True);p.brief(365,611,324,416,'Innovación y producción regional',True)
 elif n==6:
  p.title(M,119,U,72,'Movilidad y servicios: una ciudad en transformación',32);p.text(M,199,U,38,'Bajada de la noticia: prioridades, actores y plazos del proyecto urbano.',11,role='deck')
  p.body(M,252,254,425,2);p.photo(299,252,390,240);p.caption(299,499,390);p.body(299,525,390,150,3)
  p.rule(M,693,U);p.title(M,710,388,49,'Los barrios plantean sus prioridades',23);p.body(M,773,388,145,3);p.data(433,710,256,87,'DATOS DEL PROYECTO');p.body(433,815,256,104,2);p.ad(919,123)
 elif n==7:
  p.title(M,119,U,81,'La Paz: demandas y desafíos de la agenda nacional',34);p.text(M,207,U,38,'Resumen editable de la noticia y de las voces que participan en el debate.',11,role='deck')
  p.photo(M,260,388,501);p.caption(M,766,388);p.body(433,260,256,521,2)
  p.rule(M,798,U);p.brief(M,814,324,216,'Una mirada desde las regiones');p.brief(365,814,324,216,'Diálogo y próximos acuerdos')
 elif n==8:
  p.text(M,120,U,18,'PAÍS · REPORTAJE',10,True,RED,role='kicker');p.title(M,150,U,80,'El territorio y sus protagonistas: historias de la semana',32)
  p.body(M,249,254,348,2);p.photo(299,249,390,268);p.caption(299,523,390);p.text(299,558,390,49,'Una frase destacada o dato verificado del reportaje.',16,True,NAVY,bg='#edf1f6',role='quote')
  p.rule(M,623,U);p.photo(M,644,254,242,'FOTO DEL REPORTAJE');p.caption(M,892,254);p.body(M,923,254,104,2);p.title(299,644,390,66,'Una historia que continúa en las regiones',26);p.body(299,724,390,303,3)
 elif n==9:
  p.title(M,119,U,83,'Economía: indicadores y perspectivas para la gestión',34);p.text(M,210,U,38,'Bajada de la noticia económica, sus cifras y el contexto que necesita el lector.',11,role='deck')
  p.photo(M,262,388,267);p.caption(M,535,388);p.body(433,262,256,325,2);p.body(M,559,388,65,3)
  p.rule(M,641,U);p.brief(M,658,324,372,'Exportaciones y mercados',True);p.title(365,658,324,55,'Indicadores de la semana',25)
  for j,label in enumerate(['ACTIVIDAD · __ %','PRECIOS · __ %','INVERSIÓN · Bs __']):p.data(365,732+j*70,324,58,label)
  p.body(365,953,324,77,2)
 elif n==10:
  p.title(M,120,388,100,'Empresas y mercados: decisiones que marcan el rumbo',33);p.photo(433,121,256,219);p.caption(433,346,256)
  p.body(M,239,388,253,3);p.body(433,380,256,112,2);p.rule(M,510,U)
  for j,label in enumerate(['PRODUCCIÓN','EMPLEO','COMERCIO']):x=M+j*223;p.data(x,530,212,64,label+'\n__');p.title(x,611,212,60,['Actividad del sector','Trabajo y oportunidades','Mercados y consumo'][j],22);p.body(x,685,212,340)
 elif n==11:
  for j,label in enumerate(['SEGURIDAD','JUDICIAL']):
   x=M+j*334;p.text(x,122,324,21,label,12,True,RED,role='kicker');p.title(x,155,324,88,['Investigaciones y prevención: la actualidad de seguridad','Justicia: procesos, decisiones y garantías'][j],29);p.photo(x,258,324,266,label);p.caption(x,530,324);p.body(x,557,324,464,2)
  p.frame('divider',359,120,2,908,color='#cbd5e1')
 elif n==12:
  for j,label in enumerate(['MUNDO','DEPORTES','CULTURA']):
   x=M+j*223;p.text(x,122,212,21,label,12,True,RED,role='kicker');p.title(x,154,212,102,['El escenario internacional y sus protagonistas','La fecha deportiva: equipos y resultados','Arte y cultura: agenda de la semana'][j],25)
   if j<2:p.photo(x,271,212,244,label);p.caption(x,521,212);p.body(x,549,212,387);p.text(x,959,212,55,'BREVE\nDato o noticia complementaria',10,True,NAVY,bg='#edf1f6')
   else:p.body(x,271,212,258);p.photo(x,549,212,221,label);p.caption(x,776,212);p.body(x,804,212,219)
 p.footer()
 OUT.mkdir(parents=True,exist_ok=True);(OUT/f'{n:02}-pagina.latitud-template').write_text(json.dumps(p.pack(),ensure_ascii=False,indent=2))
 # Strict printable boundaries and unique geometry for every page.
 for f in p.f:assert M<=f['x'] and f['x']+f['w']<=W-M and M<=f['y'] and f['y']+f['h']<=H-M,(n,f)
signatures=[json.dumps([(f['type'],f['x'],f['y'],f['w'],f['h']) for f in p.f]) for p in P];assert len(set(signatures))==12
mapping={'nombre':'Latitud18 · Editorial v2 · 12 páginas','descripcion':'12 composiciones diferenciadas; identidad Latitud18 y retícula de periódico local.','mapping':[{'slot':n,'plantilla_id':f'tpl-l18-v2-{n:02}'} for n in range(1,13)]}
(OUT/'edicion-completa.json').write_text(json.dumps(mapping,ensure_ascii=False,indent=2))
print('12 composiciones distintas; todos los marcos dentro de márgenes.',sum(len(p.f) for p in P),'marcos.')
