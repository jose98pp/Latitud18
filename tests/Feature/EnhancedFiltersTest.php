<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Noticia;
use App\Models\Category;

class EnhancedFiltersTest extends TestCase
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
    public function it_displays_filter_interface_correctly()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // Check filter elements
        $response->assertSee('enhanced-filters');
        $response->assertSee('filters-form');
        $response->assertSee('search-input');
        $response->assertSee('category-select');
        $response->assertSee('status-select');
        $response->assertSee('Filtros y Búsqueda');
    }

    /** @test */
    public function it_filters_news_by_search_term()
    {
        $category = Category::factory()->create();
        
        $matchingNews = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Breaking Technology News',
            'contenido' => 'Latest updates in tech industry'
        ]);
        
        $nonMatchingNews = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Sports Update',
            'contenido' => 'Football match results'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['search' => 'Technology']));

        $response->assertStatus(200);
        $response->assertSee('Breaking Technology News');
        $response->assertDontSee('Sports Update');
    }

    /** @test */
    public function it_filters_news_by_category()
    {
        $techCategory = Category::factory()->create(['name' => 'Technology']);
        $sportsCategory = Category::factory()->create(['name' => 'Sports']);
        
        $techNews = Noticia::factory()->create([
            'category_id' => $techCategory->id,
            'titulo' => 'Tech News'
        ]);
        
        $sportsNews = Noticia::factory()->create([
            'category_id' => $sportsCategory->id,
            'titulo' => 'Sports News'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['category' => $techCategory->id]));

        $response->assertStatus(200);
        $response->assertSee('Tech News');
        $response->assertDontSee('Sports News');
    }

    /** @test */
    public function it_filters_news_by_publication_status()
    {
        $category = Category::factory()->create();
        
        $publishedNews = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Published News',
            'publicada' => true
        ]);
        
        $draftNews = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Draft News',
            'publicada' => false
        ]);

        // Filter for published news
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['status' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Published News');
        $response->assertDontSee('Draft News');

        // Filter for draft news
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['status' => '0']));

        $response->assertStatus(200);
        $response->assertSee('Draft News');
        $response->assertDontSee('Published News');
    }

    /** @test */
    public function it_combines_multiple_filters_correctly()
    {
        $techCategory = Category::factory()->create(['name' => 'Technology']);
        $sportsCategory = Category::factory()->create(['name' => 'Sports']);
        
        $matchingNews = Noticia::factory()->create([
            'category_id' => $techCategory->id,
            'titulo' => 'AI Technology Breakthrough',
            'publicada' => true
        ]);
        
        $nonMatchingNews1 = Noticia::factory()->create([
            'category_id' => $sportsCategory->id,
            'titulo' => 'AI in Sports',
            'publicada' => true
        ]);
        
        $nonMatchingNews2 = Noticia::factory()->create([
            'category_id' => $techCategory->id,
            'titulo' => 'AI Technology Draft',
            'publicada' => false
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', [
                'search' => 'AI',
                'category' => $techCategory->id,
                'status' => '1'
            ]));

        $response->assertStatus(200);
        $response->assertSee('AI Technology Breakthrough');
        $response->assertDontSee('AI in Sports');
        $response->assertDontSee('AI Technology Draft');
    }

    /** @test */
    public function it_displays_active_filters_correctly()
    {
        $category = Category::factory()->create(['name' => 'Technology']);
        Noticia::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', [
                'search' => 'test search',
                'category' => $category->id,
                'status' => '1'
            ]));

        $response->assertStatus(200);
        
        // Check active filters display
        $response->assertSee('active-filters');
        $response->assertSee('Filtros activos');
        $response->assertSee('test search');
        $response->assertSee('Technology');
        $response->assertSee('Publicadas');
        $response->assertSee('filter-count');
    }

    /** @test */
    public function it_shows_clear_filters_button_when_filters_are_active()
    {
        $category = Category::factory()->create();
        Noticia::factory()->create(['category_id' => $category->id]);

        // Without filters
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('clear-filters-btn');
        $response->assertDontSee('has-filters');

        // With filters
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['search' => 'test']));

        $response->assertStatus(200);
        $response->assertSee('has-filters');
    }

    /** @test */
    public function it_includes_required_filter_css_and_js()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('enhanced-filters.css');
        $response->assertSee('dynamic-filters.js');
    }

    /** @test */
    public function it_preserves_filters_across_pagination()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        Noticia::factory()->count(20)->create([
            'category_id' => $category->id,
            'titulo' => 'Test News'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', [
                'search' => 'Test',
                'category' => $category->id,
                'page' => 2
            ]));

        $response->assertStatus(200);
        
        // Check that filters are preserved in pagination links
        $content = $response->getContent();
        $this->assertStringContainsString('search=Test', $content);
        $this->assertStringContainsString('category=' . $category->id, $content);
    }

    /** @test */
    public function it_handles_empty_filter_results()
    {
        $category = Category::factory()->create();
        Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Regular News'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['search' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertSee('No se encontraron noticias');
        $response->assertSee('Intenta ajustar los filtros de búsqueda');
    }

    /** @test */
    public function it_searches_in_both_title_and_content()
    {
        $category = Category::factory()->create();
        
        $titleMatch = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Artificial Intelligence News',
            'contenido' => 'Regular content here'
        ]);
        
        $contentMatch = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Regular Title',
            'contenido' => 'This article discusses Artificial Intelligence applications'
        ]);
        
        $noMatch = Noticia::factory()->create([
            'category_id' => $category->id,
            'titulo' => 'Sports News',
            'contenido' => 'Football match results'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['search' => 'Artificial Intelligence']));

        $response->assertStatus(200);
        $response->assertSee('Artificial Intelligence News');
        $response->assertSee('Regular Title');
        $response->assertDontSee('Sports News');
    }

    /** @test */
    public function it_displays_category_options_in_filter()
    {
        $category1 = Category::factory()->create(['name' => 'Technology']);
        $category2 = Category::factory()->create(['name' => 'Sports']);
        $category3 = Category::factory()->create(['name' => 'Politics']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('Technology');
        $response->assertSee('Sports');
        $response->assertSee('Politics');
        $response->assertSee('Todas las categorías');
    }

    /** @test */
    public function it_displays_status_options_in_filter()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('Todos los estados');
        $response->assertSee('Publicadas');
        $response->assertSee('Borradores');
    }
}