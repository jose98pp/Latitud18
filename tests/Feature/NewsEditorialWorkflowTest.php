<?php
namespace Tests\Feature;

use App\Models\{Noticia, Category, User};
use App\Services\{ContentSanitizationService, NewsService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Cache, Storage};
use Tests\TestCase;

class NewsEditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_upload_uses_the_admin_session_and_public_disk(): void
    {
        Storage::fake('public');
        $this->postJson('/api/upload-image')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role'=>'user']))->postJson('/api/upload-image')->assertRedirect();
        $this->actingAs(User::factory()->create(['role'=>'admin']));
        $response = $this->postJson('/api/upload-image', ['image'=>UploadedFile::fake()->image('body.png',400,300)])->assertOk()->assertJsonPath('success',true);
        $this->assertStringContainsString('/storage/images/', $response->json('url'));
        Storage::disk('public')->assertExists('images/'.$response->json('filename'));
        $route = app('router')->getRoutes()->getByName('admin.editor.upload-image');
        $this->assertContains('web', $route->gatherMiddleware());
    }

    public function test_formatting_and_image_position_survive_but_scripts_do_not(): void
    {
        $result=app(ContentSanitizationService::class)->sanitizeContent('<p style="text-align:right"><span style="font-family:Georgia;font-size:18pt;color:#ff0000">Texto <strong>negrita</strong></span></p><img src="/storage/images/demo.png" alt="Descripción" style="width:50%;height:auto;float:right;margin-left:auto" onerror="alert(1)"><script>alert(1)</script>');
        foreach(['text-align:right','font-family:Georgia','font-size:18pt','width:50%','float:right'] as $style) $this->assertStringContainsString($style,str_replace(' ','',$result));
        $this->assertStringContainsString('<strong>negrita</strong>',$result);
        $this->assertStringNotContainsString('onerror',$result);
        $this->assertStringNotContainsString('<script',$result);
    }

    public function test_create_update_and_cache_invalidation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role'=>'admin']));
        Cache::put('homepage_data',['stale'=>true],300);
        $data=['titulo'=>'Reportaje de investigación de prueba','contenido'=>'<p style="text-align:center">'.str_repeat('Texto periodístico de prueba. ',4).'</p>','category_id'=>Category::factory()->create()->id,'publicada'=>1,'es_investigacion'=>1,'imagen'=>UploadedFile::fake()->image('main.jpg',800,600)];
        $this->post('/admin/noticias',$data)->assertSessionHasNoErrors()->assertRedirect(route('admin.noticias.index'));
        $story=Noticia::where('titulo',$data['titulo'])->firstOrFail();
        $this->assertTrue($story->es_investigacion);
        $this->assertNull(Cache::get('homepage_data'));
        $this->assertStringContainsString('text-align:center',str_replace(' ','',$story->contenido));
        unset($data['imagen'],$data['es_investigacion']);
        $data['titulo']='Reportaje corregido para revisión';
        $this->put('/admin/noticias/'.$story->id,$data)->assertSessionHasNoErrors()->assertRedirect(route('admin.noticias.index'));
        $this->assertEquals($data['titulo'],$story->fresh()->titulo);
        $this->assertFalse($story->fresh()->es_investigacion);
    }

    public function test_html_cannot_bypass_minimum_real_text_length(): void
    {
        $this->actingAs(User::factory()->create(['role'=>'admin']))->post('/admin/noticias',['titulo'=>'Título válido','contenido'=>'<p><strong>corto</strong></p>'.str_repeat('<br>',40),'category_id'=>Category::factory()->create()->id,'imagen'=>UploadedFile::fake()->image('main.jpg',200,200)])->assertSessionHasErrors('contenido');
    }

    public function test_scheduling_hides_urls_and_sitemap_then_publishes_without_cron(): void
    {
        
        $this->travelTo(now()->startOfSecond());
        $story=Noticia::factory()->create(['publicada'=>true,'publicar_en'=>now()->addSeconds(60),'es_investigacion'=>true]);
        $this->get($story->url)->assertNotFound(); 
        $this->get('/sitemap.xml')->assertDontSee($story->url,false);
        $this->assertNull(app(NewsService::class)->getHomePageData()['noticiaInvestigacion']);
        $this->travel(61)->seconds(); 
        $this->assertEquals($story->id,app(NewsService::class)->getHomePageData()['noticiaInvestigacion']->id);
        $this->get($story->url)->assertOk()->assertSee('id="articleBody"', false); 
        $this->get('/sitemap.xml')->assertSee($story->url,false);
    }

    public function test_form_saves_the_schedule_and_false_flags_without_publishing_a_draft(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role'=>'admin']));
        $date = now()->addDay()->format('Y-m-d\\TH:i');
        $data = ['titulo'=>'Noticia programada desde el formulario', 'contenido'=>str_repeat('Información periodística verificada. ',3),
            'category_id'=>Category::factory()->create()->id, 'publicada'=>1, 'es_investigacion'=>1, 'publicar_en'=>$date,
            'imagen'=>UploadedFile::fake()->image('scheduled.jpg',300,300)];
        $this->post('/admin/noticias',$data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $story=Noticia::where('titulo',$data['titulo'])->firstOrFail();
        $this->assertTrue($story->esta_programada);
        $this->assertEquals($date,$story->publicar_en->format('Y-m-d\\TH:i'));
        $this->get($story->url)->assertNotFound();
        unset($data['imagen']);
        $data['publicada']=0; $data['es_investigacion']=0;
        $this->put('/admin/noticias/'.$story->id,$data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertFalse($story->fresh()->publicada);
        $this->assertFalse($story->fresh()->es_investigacion);
    }

    public function test_latest_investigation_wins_even_when_created_in_the_same_second(): void
    {
        $this->travelTo(now()->startOfSecond());
        Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>true,'created_at'=>now()]);
        $latest=Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>true,'created_at'=>now()]);
        $this->assertEquals($latest->id,app(NewsService::class)->getHomePageData()['noticiaInvestigacion']->id);
    }

    public function test_sports_drafts_and_scheduled_stories_are_not_publicly_readable(): void
    {
        foreach ([['publicada'=>false], ['publicada'=>true,'publicar_en'=>now()->addDay()]] as $flags) {
            $story=Noticia::factory()->create($flags);
            $this->get(route('contraataque.show',['id'=>$story->id,'slug'=>\Illuminate\Support\Str::slug($story->titulo)]))->assertRedirect(route('contraataque.index'));
        }
    }

    public function test_investigation_card_never_promotes_an_unmarked_story(): void
    {
        $regular=Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>false]);
        $data=app(NewsService::class)->getHomePageData();
        $this->assertNull($data['noticiaInvestigacion']);
        $html=view('portada.categorias-secondary',$data)->render();
        $card=substr($html,strpos($html,'investiga-special-card'));
        $this->assertStringNotContainsString($regular->titulo,$card);
        $reportage=Noticia::factory()->create(['publicada'=>true,'es_investigacion'=>true]);
        $this->assertEquals($reportage->id,app(NewsService::class)->getHomePageData()['noticiaInvestigacion']->id);
        $reportage->update(['publicada'=>false]);
        $this->assertNull(app(NewsService::class)->getHomePageData()['noticiaInvestigacion']);
    }
}
