<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Noticia;
use App\Models\Category;

class EnhancedPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function it_displays_pagination_with_correct_information()
    {
        // Create test data
        $category = Category::factory()->create();
        Noticia::factory()->count(25)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('Mostrando 1 - 15 de 25 noticias');
        $response->assertSee('pagination');
    }

    /** @test */
    public function it_handles_per_page_parameter_correctly()
    {
        $category = Category::factory()->create();
        Noticia::factory()->count(30)->create(['category_id' => $category->id]);

        // Test with per_page=25
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['per_page' => 25]));

        $response->assertStatus(200);
        $response->assertSee('Mostrando 1 - 25 de 30 noticias');

        // Test with invalid per_page (should default to 15)
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['per_page' => 999]));

        $response->assertStatus(200);
        $response->assertSee('Mostrando 1 - 15 de 30 noticias');
    }

    /** @test */
    public function it_preserves_filters_in_pagination_urls()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        Noticia::factory()->count(20)->create([
            'category_id' => $category->id,
            'titulo' => 'Test News Title'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', [
                'search' => 'Test',
                'category' => $category->id,
                'status' => '1'
            ]));

        $response->assertStatus(200);
        
        // Check that pagination links preserve filters
        $content = $response->getContent();
        $this->assertStringContainsString('search=Test', $content);
        $this->assertStringContainsString('category=' . $category->id, $content);
        $this->assertStringContainsString('status=1', $content);
    }

    /** @test */
    public function it_displays_correct_pagination_controls()
    {
        $category = Category::factory()->create();
        Noticia::factory()->count(100)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        
        // Check for pagination controls
        $response->assertSee('Mostrar:'); // Page size selector
        $response->assertSee('por página');
        $response->assertSee('Ir a página:'); // Page jump
        $response->assertSee('Primera'); // First page link
        $response->assertSee('Última'); // Last page link
    }

    /** @test */
    public function it_handles_page_navigation_correctly()
    {
        $category = Category::factory()->create();
        Noticia::factory()->count(50)->create(['category_id' => $category->id]);

        // Test page 2
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['page' => 2]));

        $response->assertStatus(200);
        $response->assertSee('Mostrando 16 - 30 de 50 noticias');

        // Test last page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['page' => 4]));

        $response->assertStatus(200);
        $response->assertSee('Mostrando 46 - 50 de 50 noticias');
    }

    /** @test */
    public function it_shows_correct_pagination_for_filtered_results()
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);
        
        Noticia::factory()->count(10)->create(['category_id' => $category1->id]);
        Noticia::factory()->count(5)->create(['category_id' => $category2->id]);

        // Filter by category 2
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['category' => $category2->id]));

        $response->assertStatus(200);
        $response->assertSee('Mostrando 1 - 5 de 5 noticias');
        
        // Should not show pagination controls for single page
        $response->assertDontSee('Primera');
        $response->assertDontSee('Última');
    }

    /** @test */
    public function it_handles_empty_results_correctly()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('No se encontraron noticias');
        $response->assertDontSee('pagination');
    }

    /** @test */
    public function pagination_includes_required_css_and_js_files()
    {
        $category = Category::factory()->create();
        Noticia::factory()->count(20)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('enhanced-pagination.css');
        $response->assertSee('enhanced-pagination.js');
    }

    /** @test */
    public function it_validates_per_page_parameter_security()
    {
        $category = Category::factory()->create();
        Noticia::factory()->count(30)->create(['category_id' => $category->id]);

        // Test with allowed values
        $allowedValues = [15, 25, 50, 100];
        foreach ($allowedValues as $value) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.noticias.index', ['per_page' => $value]));
            
            $response->assertStatus(200);
        }

        // Test with disallowed values (should default to 15)
        $disallowedValues = [1, 5, 200, 1000, 'invalid'];
        foreach ($disallowedValues as $value) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.noticias.index', ['per_page' => $value]));
            
            $response->assertStatus(200);
            // Should default to 15 items per page
            $content = $response->getContent();
            $this->assertStringContainsString('de 30 noticias', $content);
        }
    }
}