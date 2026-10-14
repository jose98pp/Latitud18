"""Generate editable 12/28-page editions inspired by the October reference."""
import json,html,pathlib,hashlib
R=pathlib.Path(__file__).resolve().parents[1]; OUT=R/'database/data/latitud18-octubre'
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

class OctoberPage(Page):
 def __init__(self,n,total,section):
  self.n=n;self.total=total;self.f=[];self.section=section
 def frame(self,t,x,y,w,h,**kw):
  super().frame(t,x,y,w,h,**kw)
  self.f[-1]['id']=f'l18oct-{self.total}-p{self.n}-{len(self.f)}'
 def header(self):
  self.text(M,31,388,17,'LATITUD18 · INFORMACIÓN SIN RUIDO',9,True,NAVY,role='folio')
  self.text(433,31,256,17,f'FECHA __ / __ / ____ · PÁGINA {self.n}',8,color=NAVY,align='right',role='folio')
  self.rule(M,53,U,NAVY)
  self.text(M,64,U,32,self.section.upper(),21,True,NAVY,role='section');self.rule(M,105,U)
 def photo(self,x,y,w,h,label='FOTOGRAFÍA PRINCIPAL',portrait=False):
  super().photo(x,y,w,h,label,portrait)
  self.f[-1]['src']='/images/periodico/latitud18-clasica-photo.svg'
 def caption(self,x,y,w,text='Pie de fotografía editable · Crédito: autor'):
  super().caption(x,y,w,'Foto · Autor' if w<200 else text)
 def data(self,x,y,w,h,label):
  size=20
  while size>11 and pdfmetrics.stringWidth(label,'LayoutMontserrat',size)*1.1>w-12:size-=.5
  self.text(x,y,w,h,label,size,True,NAVY,bg='#edf1f6',align='center',role='data-box')
 def footer(self):
  self.rule(M,1053,U,NAVY)
  self.text(M,1061,U,14,f'LATITUD18 · INFORMACIÓN SIN RUIDO                 {self.n} / {self.total}',8,color=NAVY,role='folio')
 def pack(self):
  cat={'Portada':'portada','Editorial / Opinión':'editorial','Política':'politica','Santa Cruz':'santacruz','País':'pais','Economía':'economia','Seguridad / Judicial':'seguridad','Mundo / Deportes / Cultura':'mundo','Mundo':'mundo','Deportes':'deportes','Cultura':'cultura','Avisos / Edictos':'avisos'}[self.section]
  return {'id':f'tpl-l18-oct-{self.total}-{self.n:02}','name':f'Latitud18 · Clásica {self.total} · {self.n:02} · {self.section}','category':cat,'description':'Retícula de periódico local: apertura, noticias secundarias y columnas independientes. Diseño editable de Latitud18.','preview_color':NAVY,'configuracion':dict(CFG,section_label=self.section,edition_family='clasica-octubre',edition_pages=self.total),'frames':self.f}
 def story(self,x,y,w,h,title,photo_h=0,cols=2):
  self.title(x,y,w,62,title,26)
  if photo_h:
   self.photo(x,y+71,w,photo_h);self.caption(x,y+photo_h+76,w)
   self.body(x,y+photo_h+101,w,h-photo_h-101,cols)
  else:self.body(x,y+72,w,h-72,cols)
 def legal(self,y,h,cols,label):
  self.text(M+6,y+5,U-12,24,label,12,True,NAVY,align='center',role='notice-heading')
  self.body(M+8,y+38,U-16,h-46,cols)
  self.rule(M,y,U,NAVY);self.rule(M,y+h,U,NAVY)
  self.frame('divider',M,y,1,h,color='#0B1F3A');self.frame('divider',M+U-1,y,1,h,color='#0B1F3A')

SECTIONS_12=['Portada','Editorial / Opinión','Política','Política','Santa Cruz','Santa Cruz','País','País','Economía','Economía','Seguridad / Judicial','Mundo / Deportes / Cultura']
SECTIONS_28=SECTIONS_12[:11]+['Mundo','Mundo','Deportes','Deportes','Cultura']+['Avisos / Edictos']*12
OUT.mkdir(parents=True,exist_ok=True)
for total in (12,28):
 pages=[]
 for n,section in enumerate(SECTIONS_12 if total==12 else SECTIONS_28,1):
  p=OctoberPage(n,total,section);pages.append(p)
  if n>1:p.header()
  if n==1:
   p.text(M,31,U,17,f'EDICIÓN N.º __ · SEMANA DEL __ AL __ · {total} PÁGINAS',9,role='folio')
   p.text(M,54,525,96,'LATITUD',77,True,NAVY,role='brand');p.text(568,66,121,84,'18',48,True,'#fff',RED,align='center',role='brand')
   p.text(M,153,U,25,'INFORMACIÓN SIN RUIDO',16,True,NAVY,role='brand');p.rule(M,188,U,NAVY)
   p.text(M,198,U,17,'SANTA CRUZ DE LA SIERRA · BOLIVIA · PRECIO Bs __ · LATITUD18.COM.BO',8,role='folio')
   p.title(M,226,U,133,'La agenda de la semana: decisiones que marcan el rumbo del país',45)
   p.text(M,365,U,44,'Bajada de portada: explique los hechos principales, su alcance y por qué son relevantes para los lectores.',12,role='deck')
   p.photo(M,423,496,380);p.caption(M,809,496)
   for j,(sec,title) in enumerate([('SANTA CRUZ','Proyectos y servicios para la ciudad'),('ECONOMÍA','Indicadores y perspectivas de la gestión'),('SEGURIDAD','Investigaciones y prevención')]):
    y=423+j*137;p.text(543,y,146,19,sec,10,True,RED,role='kicker');p.title(543,y+23,146,63,title,21);p.photo(543,y+90,146,39)
   p.rule(M,839,U);p.title(M,853,U,51,'País: las voces y los desafíos de las regiones',27);p.ad(917,123)
  elif n==2:
   p.text(M,120,388,21,'EDITORIAL',11,True,RED,role='kicker');p.title(M,148,388,82,'Periodismo que informa y acompaña a la ciudadanía',31)
   p.body(M,244,388,294,3);p.photo(433,123,256,150);p.caption(433,279,256)
   p.text(433,309,256,20,'OPINIÓN',11,True,RED,role='kicker');p.title(433,336,256,66,'La mirada de nuestros columnistas',25);p.body(433,414,256,124,2)
   p.rule(M,553,U);p.text(M,567,U,23,'ANÁLISIS · IDEAS PARA EL DEBATE',12,True,'#fff',RED,role='kicker')
   p.title(M,602,U,53,'Instituciones, participación y desafíos compartidos',29);p.body(M,672,U,270,5)
   p.rule(M,958,U,NAVY);p.text(M,976,U,53,'REDACCIÓN LATITUD18 · Dirección editorial · Consejo de redacción · Contacto y créditos editables',11,True,NAVY,bg='#edf1f6',role='credits')
  elif n==3:
   p.title(M,121,U,101,'Política: acuerdos y decisiones de la semana legislativa',39)
   p.text(M,229,U,41,'Resumen de la noticia principal y de las posiciones de sus protagonistas.',11,role='deck')
   p.body(M,285,254,369,2);p.photo(299,285,390,245);p.caption(299,537,390);p.body(299,561,390,92,3)
   p.rule(M,672,U);p.story(M,686,388,342,'El alcance de las propuestas y sus próximos pasos',90,3);p.story(433,686,256,342,'Las voces del debate público',113,2)
  elif n==4:
   p.title(M,121,522,109,'El debate público: actores, propuestas y sus efectos',36);p.photo(567,123,122,220);p.caption(567,349,122)
   p.body(M,245,254,397,2);p.photo(299,245,254,180);p.caption(299,431,254);p.body(299,457,254,185,2)
   p.title(567,389,122,84,'Claves de la jornada',22);p.body(567,487,122,158)
   p.rule(M,663,U);p.title(M,679,U,60,'Reformas e instituciones: una mirada de contexto',29)
   p.body(M,756,254,272,2);p.photo(299,756,390,191);p.caption(299,953,390);p.text(299,978,390,48,'CONTEXTO · Datos comprobados y antecedentes para entender la noticia.',11,True,NAVY,bg='#edf1f6')
  elif n==5:
   p.title(M,121,U,99,'Santa Cruz: servicios y prioridades en la agenda ciudadana',37)
   p.text(M,228,U,35,'Bajada editable de la noticia urbana: protagonistas, plazos e impacto.',11,role='deck')
   p.photo(M,278,U,260);p.caption(M,544,U);p.body(M,571,U,91,5)
   p.rule(M,680,U);p.story(M,696,388,331,'Los barrios presentan sus prioridades',90,3);p.story(433,696,256,331,'Innovación y servicios municipales',80,2)
  elif n==6:
   p.title(M,122,522,105,'La ciudad se transforma con la participación de sus vecinos',36)
   p.story(567,122,122,456,'Agenda de los distritos',102,1)
   p.body(M,244,254,364,2);p.photo(299,244,254,201);p.caption(299,451,254);p.body(299,478,254,126,2)
   p.rule(M,627,U);p.title(M,645,U,66,'Espacios públicos: obras, movilidad y vida de barrio',30)
   p.body(M,727,254,301,2);p.photo(299,727,390,214);p.caption(299,947,390);p.body(299,974,390,54,3)
  elif n==7:
   p.story(M,121,388,548,'País: las regiones y los desafíos de su desarrollo',220,3)
   p.story(433,121,256,548,'Las comunidades plantean sus demandas',150,2)
   p.rule(M,689,U);p.story(M,706,388,321,'Infraestructura y proyectos estratégicos',89,3);p.story(433,706,256,321,'Una mirada desde las provincias',74,2)
  elif n==8:
   p.title(M,121,U,97,'El territorio y sus protagonistas: la agenda nacional',38)
   p.text(M,227,U,34,'Bajada editable del reportaje con sus antecedentes y fuentes consultadas.',11,role='deck')
   p.body(M,277,254,386,2);p.photo(299,277,390,227);p.caption(299,510,390);p.body(299,537,390,126,3)
   p.rule(M,680,U);p.story(M,696,254,331,'El contexto de la noticia',0,2);p.story(299,696,254,331,'Proyectos en las regiones',91,2);p.story(567,696,122,331,'Agenda nacional',66,1)
  elif n==9:
   p.title(M,121,522,102,'Economía: actividad productiva y perspectivas para la gestión',36)
   p.photo(M,240,388,218);p.caption(M,464,388);p.body(M,491,388,185,3)
   p.title(433,241,120,79,'Datos de la semana',21);p.body(433,333,120,343)
   p.story(567,121,122,555,'Mercados y precios',118,1)
   p.rule(M,695,U);p.story(M,712,388,315,'Exportaciones y mercados regionales',89,3);p.story(433,712,256,315,'Empresas y oportunidades',70,2)
  elif n==10:
   p.title(M,121,U,94,'Producción, inversión y empleo: los retos del sector',37)
   p.body(M,232,254,364,2);p.photo(299,232,390,219);p.caption(299,457,390);p.body(299,484,390,112,3)
   p.rule(M,613,U)
   for j,label in enumerate(['PRODUCCIÓN · __ %','EMPLEO · __ %','INVERSIÓN · Bs __']):p.data(M+j*223,631,212,61,label)
   p.story(M,711,254,316,'La actividad de las empresas',0,2);p.story(299,711,254,316,'El trabajo y las oportunidades',73,2);p.story(567,711,122,316,'Cifras y contexto',0,1)
  elif n==11:
   p.title(M,121,U,99,'Seguridad y justicia: investigaciones, procesos y prevención',36)
   p.text(M,227,U,36,'Resumen de hechos verificados y del estado de las investigaciones.',11,role='deck')
   p.photo(M,278,388,219);p.caption(M,503,388);p.body(M,530,388,117,3);p.body(433,278,256,369,2)
   p.rule(M,666,U);p.story(M,682,388,345,'Una mirada a los procesos judiciales',100,3);p.story(433,682,256,345,'Prevención y seguridad ciudadana',133,2)
  elif n==12 and total==12:
   for j,label in enumerate(['MUNDO','DEPORTES','CULTURA']):
    x=M+j*223;p.text(x,121,212,21,label,11,True,RED,role='kicker');p.title(x,154,212,100,['La actualidad internacional y sus protagonistas','La jornada deportiva: equipos y resultados','Arte y cultura: la agenda de la semana'][j],26)
    p.photo(x,270+j*22,212,177+j*18);p.caption(x,453+j*40,212);p.body(x,482+j*40,212,430-j*40)
    p.text(x,935,212,89,'BREVE · Espacio editable para otra noticia de la sección.',11,True,NAVY,bg='#edf1f6')
  elif n==12:
   p.story(M,121,388,569,'Mundo: los acontecimientos que marcan la agenda internacional',242,3)
   p.story(433,121,256,569,'Contexto y protagonistas de la noticia',173,2)
   p.rule(M,710,U);p.story(M,727,388,300,'El escenario global y sus efectos',66,3);p.story(433,727,256,300,'Una mirada a la región',0,2)
  elif n==13:
   p.title(M,121,U,105,'El escenario internacional: elecciones, acuerdos y nuevos desafíos',36)
   p.body(M,241,122,387);p.photo(167,241,388,215);p.caption(167,462,388);p.body(167,488,388,140,3)
   p.story(569,241,120,387,'La noticia en cifras',75,1)
   p.rule(M,648,U);p.story(M,665,324,362,'Los protagonistas y sus propuestas',91,2);p.story(365,665,324,362,'Los efectos en la región',139,2)
  elif n==14:
   p.title(M,121,U,94,'Deportes: una jornada que reúne a equipos e hinchas',38)
   p.body(M,233,122,419);p.photo(167,233,388,251);p.caption(167,490,388);p.body(167,517,388,135,3)
   p.story(569,233,120,419,'Agenda deportiva',112,1)
   p.rule(M,673,U);p.story(M,690,388,337,'El campeonato y sus protagonistas',102,3);p.story(433,690,256,337,'Resultados y próximos encuentros',68,2)
  elif n==15:
   p.title(M,121,U,96,'Los protagonistas del deporte local y sus nuevos desafíos',37)
   p.body(M,234,254,306,2);p.photo(299,234,390,232);p.caption(299,472,390);p.body(299,499,390,96,3)
   p.photo(M,613,324,142);p.caption(M,761,324);p.photo(365,613,324,142);p.caption(365,761,324)
   p.body(M,788,U,82,5);p.ad(897,143)
  elif n==16:
   p.title(M,121,U,98,'Cultura: historias, expresiones y encuentros de nuestra ciudad',37)
   p.photo(M,234,U,264);p.caption(M,504,U)
   p.body(M,532,U,178,5);p.rule(M,728,U)
   p.story(M,744,324,283,'La agenda de artistas y espacios culturales',55,2);p.story(365,744,324,283,'Lecturas, cine y patrimonio',0,2)
  else:
   k=n-17
   if k==0:
    # Opening of the legal supplement, with a separate title instead of a page screenshot.
    p.text(M,121,U,19,'SUPLEMENTO · AVISOS Y EDICTOS',11,True,RED,role='kicker')
    p.title(M,151,U,92,'Avisos y edictos: información para la comunidad',38)
    p.text(M,251,U,41,'PLANTILLA DE MUESTRA · Sustituya los bloques por los documentos aprobados para publicación.',10,role='deck')
    p.legal(310,327,4,'AVISO / EDICTO · TÍTULO EDITABLE');p.legal(656,371,3,'AVISO / EDICTO · SEGUNDO DOCUMENTO')
   elif k==1:
    p.title(M,121,U,61,'Avisos de la comunidad y comunicados',29)
    for j,(x,w) in enumerate([(M,122),(167,254),(435,254)]):
     p.text(x,201,w,33,['AVISO','COMUNICADO','CONVOCATORIA'][j],12,True,'#fff',NAVY,align='center');p.body(x,250,w,334,1 if j==0 else 2)
    p.legal(607,420,4,'AVISO / EDICTO · DOCUMENTO EDITABLE')
   else:
    # Different notice placements follow the source's full-page and stacked legal blocks.
    patterns=[[(121,285,4),(424,284,3),(726,301,4)],[(121,438,3),(577,450,4)],[(121,209,3),(348,331,4),(697,330,3)],[(121,906,4)],[(121,372,3),(511,254,4),(783,244,3)],[(121,264,4),(403,419,3),(840,187,4)],[(121,521,3),(660,367,4)],[(121,324,4),(463,564,3)],[(121,238,3),(377,281,4),(676,351,3)],[(121,362,4),(501,390,3)]]
    for j,(y,h,cols) in enumerate(patterns[k-2]):p.legal(y,h,cols,f'AVISO / EDICTO · DOCUMENTO {j+1}')
    if k==11:p.ad(910,130)
  p.footer()
  for f in p.f:assert M<=f['x'] and f['x']+f['w']<=W-M and M<=f['y'] and f['y']+f['h']<=H-M,(total,n,f)
  (OUT/f'{total}-{n:02}.latitud-template').write_text(json.dumps(p.pack(),ensure_ascii=False,indent=2))
 mapping={'nombre':f'Latitud18 · Clásica Octubre · {total} páginas','descripcion':f'{total} páginas editables inspiradas en la distribución del periódico local. '+('Incluye 12 páginas de avisos y edictos.' if total==28 else 'Secciones habituales de Latitud18.'),'mapping':[{'slot':i,'plantilla_id':f'tpl-l18-oct-{total}-{i:02}'} for i in range(1,total+1)]}
 (OUT/f'edicion-{total}.json').write_text(json.dumps(mapping,ensure_ascii=False,indent=2))
 signatures=[json.dumps([(f['type'],f['x'],f['y'],f['w'],f['h']) for f in p.f]) for p in pages]
 assert len(set(signatures))==total,(total,'distribuciones repetidas')
 print(f'{total} páginas, {sum(len(p.f) for p in pages)} marcos, {len(set(signatures))} composiciones diferentes.')
