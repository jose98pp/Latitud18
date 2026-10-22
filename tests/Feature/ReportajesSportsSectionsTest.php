<?php

namespace Tests\Feature;

use App\Models\{Category, Noticia, User, ArticuloOpinion, Columnista};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Http, Storage};
use Tests\TestCase;

class ReportajesSportsSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*'=>Http::response(['events'=>[]])]);
    }

    public function test_reportajes_archive_excludes_regular_draft_and_scheduled_stories(): void
    {
        $live=Noticia::factory()->create(['titulo'=>'Investigación pública verificada','publicada'=>true,'es_investigacion'=>true]);
        $normal=Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>false]);
        $draft=Noticia::factory()->create(['publicada'=>false,'es_investigacion'=>true]);
        $future=Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>true,'publicar_en'=>now()->addDay()]);
        $response=$this->get('/reportajes')->assertOk()->assertSee($live->titulo);
        $items=$response->viewData('reportajes');
        $this->assertEquals([$live->id],$items->pluck('id')->all());
        $response->assertDontSee($normal->titulo)->assertDontSee($draft->titulo)->assertDontSee($future->titulo);
        $this->get('/sitemap.xml')->assertSee(route('reportajes.index'),false);
    }

    public function test_archive_search_category_and_pagination_keep_the_filters(): void
    {
        $category=Category::factory()->create(['name'=>'Economía']);
        $matching=Noticia::factory()->count(14)->create(['category_id'=>$category->id,'publicada'=>true,'es_investigacion'=>true,'titulo'=>'Investigación sobre el agua']);
        Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>true,'titulo'=>'Investigación cultural']);
        $response=$this->get('/reportajes?q=agua&categoria='.$category->id)->assertOk();
        $items=$response->viewData('reportajes');
        $this->assertEquals(14,$items->total());
        $this->assertCount(12,$items);
        $this->assertStringContainsString('q=agua',$items->nextPageUrl());
        $this->assertStringContainsString('categoria='.$category->id,$items->nextPageUrl());
        $this->get('/reportajes?q=agua&categoria='.$category->id.'&page=2')->assertOk()->assertViewHas('reportajes',fn($p)=>$p->count()===2);
        $this->get('/reportajes?q=ningunresultado')->assertOk()->assertSee('No encontramos reportajes');
    }

    public function test_explicit_sports_classification_wins_over_keywords_and_has_no_fallback(): void
    {
        $sports=Category::factory()->create(['name'=>'Deportes']);
        $motor=Noticia::factory()->create(['titulo'=>'Bolívar y La Verde visitan el Dakar','category_id'=>$sports->id,'publicada'=>true,'seccion_deportiva'=>'motores']);
        Noticia::factory()->create(['titulo'=>'Bolívar gana la liga boliviana','category_id'=>$sports->id,'publicada'=>true,'seccion_deportiva'=>null]);
        Noticia::factory()->create(['category_id'=>$sports->id,'publicada'=>false,'seccion_deportiva'=>'motores']);
        Noticia::factory()->create(['category_id'=>$sports->id,'publicada'=>true,'publicar_en'=>now()->addDay(),'seccion_deportiva'=>'motores']);
        $response=$this->get('/contraataque/seccion/motores')->assertOk();
        $this->assertEquals([$motor->id],$response->viewData('noticias')->pluck('id')->all());
        $response->assertDontSee('PARTIDOS Y RESULTADOS')->assertDontSee('Partidos y resultados · Bolivia');
        $response->assertSee('vendor/bootstrap/bootstrap.min.css', false)->assertDontSee('cdn.tailwindcss.com', false);
        $this->get($motor->url)->assertOk()->assertSee(route('contraataque.seccion', 'motores'), false)
            ->assertDontSee('PARTIDOS EN DIRECTO')->assertDontSee('PARTIDOS Y RESULTADOS');
        Http::assertNothingSent();
        $this->get('/contraataque/seccion/futbol-boliviano')->assertOk()->assertViewHas('noticias',fn($p)=>$p->total()===0)->assertSee('Todavía no hay publicaciones');
        $this->get('/contraataque/seccion/no-existe')->assertNotFound();
    }

    public function test_category_nine_is_not_assumed_to_be_sports_and_non_sports_cannot_leak(): void
    {
        $general=Category::factory()->create(['id'=>9,'name'=>'Entretenimiento']);
        $sports=Category::factory()->create(['id'=>31,'name'=>'Deportes']);
        $regular=Noticia::factory()->create(['category_id'=>$general->id,'publicada'=>true,'seccion_deportiva'=>'motores']);
        $motor=Noticia::factory()->create(['category_id'=>$sports->id,'publicada'=>true,'seccion_deportiva'=>'motores']);
        $this->assertStringNotContainsString('/contraataque/',$regular->url);
        $this->assertStringContainsString('/contraataque/',$motor->url);
        $this->get('/contraataque/seccion/motores')->assertViewHas('noticias',fn($p)=>$p->pluck('id')->all()===[$motor->id]);
        $this->get(route('contraataque.show',['id'=>$regular->id]))->assertRedirect($regular->url)->assertStatus(301);
        $this->get('/contraataque/seccion/deportes')->assertRedirect(route('contraataque.index'))->assertStatus(301);
    }

    public function test_news_form_requires_and_persists_the_section_and_clears_it_when_category_changes(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role'=>'admin']));
        $sports=Category::factory()->create(['name'=>'Deportes']);
        $data=['titulo'=>'Crónica deportiva de prueba','contenido'=>str_repeat('Información deportiva verificada. ',4),'category_id'=>$sports->id,'publicada'=>1,'imagen'=>UploadedFile::fake()->image('cover.jpg',300,300)];
        $this->post('/admin/noticias',$data)->assertSessionHasErrors('seccion_deportiva');
        $data['seccion_deportiva']='incorrecta';
        $this->post('/admin/noticias',$data)->assertSessionHasErrors('seccion_deportiva');
        $data['seccion_deportiva']='la-verde';
        $this->post('/admin/noticias',$data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $story=Noticia::where('titulo',$data['titulo'])->firstOrFail();
        $this->assertEquals('la-verde',$story->seccion_deportiva);
        $this->get('/admin/noticias/'.$story->id.'/edit')->assertOk()->assertSee('Sección de Contra Ataque');
        unset($data['imagen']);
        $data['category_id']=Category::factory()->create(['name'=>'Cultura'])->id;
        $this->put('/admin/noticias/'.$story->id,$data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertNull($story->fresh()->seccion_deportiva);
    }

    public function test_each_nav_section_and_its_search_only_show_the_assigned_sport(): void
    {
        $sports=Category::factory()->create(['name'=>'Deportes']);
        foreach (array_keys(\App\Support\SportsSections::LABELS) as $section) {
            $story=Noticia::factory()->create(['category_id'=>$sports->id,'publicada'=>true,'seccion_deportiva'=>$section,'titulo'=>'Cobertura exclusiva de '.$section]);
            $response=$this->get('/contraataque/seccion/'.$section.'?q=exclusiva')->assertOk();
            $this->assertEquals([$story->id],$response->viewData('noticias')->pluck('id')->all());
        }
    }

    public function test_sports_opinion_is_explicit_and_requires_an_active_author(): void
    {
        $author=Columnista::create(['nombre'=>'Autora de deportes','activo'=>true]);
        $data=['columnista_id'=>$author->id,'tipo'=>'COLUMNA','contenido'=>'Análisis deportivo de prueba','publicado'=>true];
        $sports=ArticuloOpinion::create($data+['titulo'=>'Análisis deportivo publicado','es_deportiva'=>true]);
        ArticuloOpinion::create($data+['titulo'=>'Opinión general','es_deportiva'=>false]);
        ArticuloOpinion::create(array_merge($data,['titulo'=>'Borrador deportivo','es_deportiva'=>true,'publicado'=>false]));
        $this->get('/contraataque/seccion/opinion')->assertOk()->assertViewHas('noticias',fn($p)=>$p->pluck('id')->all()===[$sports->id])->assertSee($author->nombre);
        $author->update(['activo'=>false]);
        $this->get('/contraataque/seccion/opinion')->assertOk()->assertViewHas('noticias',fn($p)=>$p->total()===0);
    }

    public function test_opinion_admin_can_assign_and_remove_sports_visibility(): void
    {
        $this->actingAs(User::factory()->create(['role'=>'admin']));
        $author=Columnista::create(['nombre'=>'Autor de prueba','activo'=>true]);
        $data=['columnista_id'=>$author->id,'tipo'=>'COLUMNA','titulo'=>'Opinión sobre la selección','contenido'=>'Texto de análisis deportivo.','es_deportiva'=>1,'publicado'=>1];
        $this->post('/admin/opinion/articulos',$data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $article=ArticuloOpinion::where('titulo',$data['titulo'])->firstOrFail();
        $this->assertTrue($article->es_deportiva);
        unset($data['es_deportiva']);
        $this->put('/admin/opinion/articulos/'.$article->id,$data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertFalse($article->fresh()->es_deportiva);
    }
}
