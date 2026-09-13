<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Noticia;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaginationFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create category
        $this->category = Category::factory()->create();
        
        // Create multiple news items for pagination testing
        Noticia::factory()->count(50)->create([
            'category_id' => $this->category->id,
            'user_id' => $this->admin->id,
            'publicada' => true
        ]);
    }

    /** @test */
    public function pagination_loads_correctly_on_admin_news_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('enhanced-pagination-wrapper');
        $response->assertSee('Mostrando');
        $response->assertSee('de 50 noticias');
    }

    /** @test */
    public function pagination_works_with_page_parameter()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['page' => 2]));

        $response->assertStatus(200);
        $response->assertSee('enhanced-pagination-wrapper');
    }

    /** @test */
    public function pagination_works_with_per_page_parameter()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', ['per_page' => 25]));

        $response->assertStatus(200);
        $response->assertSee('enhanced-pagination-wrapper');
        $response->assertSee('de 50 noticias');
    }

    /** @test */
    public function pagination_preserves_filters()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index', [
                'search' => 'test',
                'category' => $this->category->id,
                'page' => 2
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function simple_pagination_script_loads()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.noticias.index'));

        $response->assertStatus(200);
        $response->assertSee('simple-pagination.js');
    }
}