<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Noticia;
use App\Models\Category;

class NewsViewsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function it_displays_news_in_card_format_by_default()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Test News Title',
            'contenido' => 'Test news content for display',
            'publicada' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('news-cards-grid');
        $response->assertSee('news-card');
        $response->assertSee('Test News Title');
        $response->assertSee('Test Category');
    }

    /** @test */
    public function it_includes_required_css_and_js_files()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('news-cards.css');
        $response->assertSee('news-views.css');
        $response->assertSee('news-views.js');
        $response->assertSee('performance-optimization.js');
    }

    /** @test */
    public function it_displays_view_toggle_buttons()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('view-toggle');
        $response->assertSee('grid-view-btn');
        $response->assertSee('list-view-btn');
        $response->assertSee('Cuadrícula');
        $response->assertSee('Lista');
    }

    /** @test */
    public function it_shows_correct_news_card_elements()
    {
        $category = Category::factory()->create(['name' => 'Technology']);
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Breaking Tech News',
            'contenido' => 'This is a detailed news article about technology trends.',
            'publicada' => true,
            'imagen' => 'test-image.jpg'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // Check card elements
        $response->assertSee('news-card-image');
        $response->assertSee('news-card-content');
        $response->assertSee('news-card-title');
        $response->assertSee('news-card-description');
        $response->assertSee('category-badge');
        $response->assertSee('actions-dropdown');
        
        // Check content
        $response->assertSee('Breaking Tech News');
        $response->assertSee('Technology');
        $response->assertSee('Publicada');
    }

    /** @test */
    public function it_displays_status_badges_correctly()
    {
        $category = Category::factory()->create();
        
        // Published news
        $publishedNews = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Published News',
            'publicada' => true
        ]);
        
        // Draft news
        $draftNews = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Draft News',
            'publicada' => false
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('Publicada');
        $response->assertSee('Borrador');
    }

    /** @test */
    public function it_shows_action_buttons_for_each_news_item()
    {
        $category = Category::factory()->create();
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'publicada' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // Check for action elements
        $response->assertSee('actions-dropdown');
        $response->assertSee('actions-trigger');
        $response->assertSee('actions-menu');
        
        // Check for action links
        $response->assertSee('Ver noticia');
        $response->assertSee('Editar');
        $response->assertSee('Eliminar');
    }

    /** @test */
    public function it_handles_news_without_images_correctly()
    {
        $category = Category::factory()->create();
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'News Without Image',
            'imagen' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('fa-image'); // Default image icon
    }

    /** @test */
    public function it_displays_news_metadata_correctly()
    {
        $category = Category::factory()->create(['name' => 'Sports']);
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Sports News',
            'created_at' => now()->subDays(2)
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // Check metadata
        $response->assertSee('Sports');
        $response->assertSee($noticia->created_at->format('d/m/Y'));
        $response->assertSee('ID: ' . $noticia->id);
    }

    /** @test */
    public function it_truncates_long_content_appropriately()
    {
        $category = Category::factory()->create();
        $longContent = str_repeat('This is a very long news article content that should be truncated in the card view. ', 20);
        
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'News with Long Content',
            'contenido' => $longContent
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // Should see truncated content, not the full content
        $response->assertSee('News with Long Content');
        $response->assertDontSee($longContent); // Full content should not be visible
    }

    /** @test */
    public function it_shows_empty_state_when_no_news_exist()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('No se encontraron noticias');
        $response->assertSee('Intenta ajustar los filtros de búsqueda');
        $response->assertSee('Limpiar Filtros');
    }

    /** @test */
    public function it_includes_lazy_loading_attributes_for_images()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('test-image.jpg', 'fake content');

        $category = Category::factory()->create();
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'imagen' => 'test-image.jpg'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('lazy-image');
        $response->assertSee('data-src');
        $response->assertSee('loading="lazy"', false);
    }

    /** @test */
    public function it_displays_image_validation_indicators()
    {
        $category = Category::factory()->create();
        
        // Create news with image info
        $noticia = Noticia::factory()->create([
            'category_id' => $category->id,
            'imagen' => 'valid-image.jpg'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // The response should include image validation elements
        // Note: Actual validation depends on the ImageStorageService
        $content = $response->getContent();
        $this->assertStringContainsString('news-card-image', $content);
    }
}