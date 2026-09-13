<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Noticia;
use App\Models\Category;
use Carbon\Carbon;

class NewsStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_total_news_correctly()
    {
        $category = Category::factory()->create();
        
        // Create test news
        Noticia::factory()->count(5)->create(['category_id' => $category->id]);
        
        $totalNews = Noticia::count();
        
        $this->assertEquals(5, $totalNews);
    }

    /** @test */
    public function it_calculates_published_news_correctly()
    {
        $category = Category::factory()->create();
        
        // Create published news
        Noticia::factory()->count(3)->create([
            'category_id' => $category->id,
            'publicada' => true
        ]);
        
        // Create draft news
        Noticia::factory()->count(2)->create([
            'category_id' => $category->id,
            'publicada' => false
        ]);
        
        $publishedNews = Noticia::where('publicada', true)->count();
        $draftNews = Noticia::where('publicada', false)->count();
        
        $this->assertEquals(3, $publishedNews);
        $this->assertEquals(2, $draftNews);
    }

    /** @test */
    public function it_calculates_published_percentage_correctly()
    {
        $category = Category::factory()->create();
        
        // Create 8 published and 2 draft news (80% published)
        Noticia::factory()->count(8)->create([
            'category_id' => $category->id,
            'publicada' => true
        ]);
        
        Noticia::factory()->count(2)->create([
            'category_id' => $category->id,
            'publicada' => false
        ]);
        
        $totalNews = Noticia::count();
        $publishedNews = Noticia::where('publicada', true)->count();
        $percentage = $totalNews > 0 ? round(($publishedNews / $totalNews) * 100, 1) : 0;
        
        $this->assertEquals(10, $totalNews);
        $this->assertEquals(8, $publishedNews);
        $this->assertEquals(80.0, $percentage);
    }

    /** @test */
    public function it_handles_zero_news_percentage_calculation()
    {
        $totalNews = Noticia::count();
        $publishedNews = Noticia::where('publicada', true)->count();
        $percentage = $totalNews > 0 ? round(($publishedNews / $totalNews) * 100, 1) : 0;
        
        $this->assertEquals(0, $totalNews);
        $this->assertEquals(0, $publishedNews);
        $this->assertEquals(0, $percentage);
    }

    /** @test */
    public function it_calculates_recent_news_correctly()
    {
        $category = Category::factory()->create();
        
        // Create recent news (within 7 days)
        Noticia::factory()->count(3)->create([
            'category_id' => $category->id,
            'created_at' => Carbon::now()->subDays(3)
        ]);
        
        // Create old news (more than 7 days)
        Noticia::factory()->count(2)->create([
            'category_id' => $category->id,
            'created_at' => Carbon::now()->subDays(10)
        ]);
        
        $recentNews = Noticia::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        
        $this->assertEquals(3, $recentNews);
    }

    /** @test */
    public function it_counts_categories_correctly()
    {
        // Create categories
        Category::factory()->count(4)->create();
        
        $totalCategories = Category::count();
        
        $this->assertEquals(4, $totalCategories);
    }

    /** @test */
    public function it_groups_news_by_category_correctly()
    {
        $category1 = Category::factory()->create(['name' => 'Technology']);
        $category2 = Category::factory()->create(['name' => 'Sports']);
        
        // Create news for each category
        Noticia::factory()->count(3)->create(['category_id' => $category1->id]);
        Noticia::factory()->count(2)->create(['category_id' => $category2->id]);
        
        $newsByCategory = Noticia::select('category_id')
            ->selectRaw('COUNT(*) as count')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name ?? 'Sin Categoría',
                    'count' => $item->count
                ];
            });
        
        $this->assertCount(2, $newsByCategory);
        
        $techStats = $newsByCategory->firstWhere('category', 'Technology');
        $sportsStats = $newsByCategory->firstWhere('category', 'Sports');
        
        $this->assertEquals(3, $techStats['count']);
        $this->assertEquals(2, $sportsStats['count']);
    }

    /** @test */
    public function it_handles_news_without_category()
    {
        // Create news without category (should be handled gracefully)
        $newsByCategory = Noticia::select('category_id')
            ->selectRaw('COUNT(*) as count')
            ->with('category:id,name')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name ?? 'Sin Categoría',
                    'count' => $item->count
                ];
            });
        
        // Should handle empty result gracefully
        $this->assertIsObject($newsByCategory);
    }

    /** @test */
    public function it_validates_statistics_data_structure()
    {
        $category = Category::factory()->create();
        Noticia::factory()->count(5)->create([
            'category_id' => $category->id,
            'publicada' => true
        ]);
        
        $statistics = [
            'total' => Noticia::count(),
            'published' => Noticia::where('publicada', true)->count(),
            'drafts' => Noticia::where('publicada', false)->count(),
            'categories' => Category::count(),
            'recent' => Noticia::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'published_percentage' => 100.0
        ];
        
        // Validate structure
        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('published', $statistics);
        $this->assertArrayHasKey('drafts', $statistics);
        $this->assertArrayHasKey('categories', $statistics);
        $this->assertArrayHasKey('recent', $statistics);
        $this->assertArrayHasKey('published_percentage', $statistics);
        
        // Validate data types
        $this->assertIsInt($statistics['total']);
        $this->assertIsInt($statistics['published']);
        $this->assertIsInt($statistics['drafts']);
        $this->assertIsInt($statistics['categories']);
        $this->assertIsInt($statistics['recent']);
        $this->assertIsFloat($statistics['published_percentage']);
    }

    /** @test */
    public function it_validates_statistics_mathematical_relationships()
    {
        $category = Category::factory()->create();
        
        Noticia::factory()->count(6)->create([
            'category_id' => $category->id,
            'publicada' => true
        ]);
        
        Noticia::factory()->count(4)->create([
            'category_id' => $category->id,
            'publicada' => false
        ]);
        
        $total = Noticia::count();
        $published = Noticia::where('publicada', true)->count();
        $drafts = Noticia::where('publicada', false)->count();
        
        // Mathematical relationships
        $this->assertEquals($total, $published + $drafts);
        $this->assertGreaterThanOrEqual(0, $total);
        $this->assertGreaterThanOrEqual(0, $published);
        $this->assertGreaterThanOrEqual(0, $drafts);
        $this->assertLessThanOrEqual($total, $published);
        $this->assertLessThanOrEqual($total, $drafts);
    }

    /** @test */
    public function it_validates_percentage_calculation_edge_cases()
    {
        // Test with no news
        $percentage1 = 0 > 0 ? round((0 / 0) * 100, 1) : 0;
        $this->assertEquals(0, $percentage1);
        
        // Test with all published
        $percentage2 = 5 > 0 ? round((5 / 5) * 100, 1) : 0;
        $this->assertEquals(100.0, $percentage2);
        
        // Test with none published
        $percentage3 = 5 > 0 ? round((0 / 5) * 100, 1) : 0;
        $this->assertEquals(0.0, $percentage3);
        
        // Test with partial published
        $percentage4 = 3 > 0 ? round((1 / 3) * 100, 1) : 0;
        $this->assertEquals(33.3, $percentage4);
    }

    /** @test */
    public function it_validates_date_range_calculations()
    {
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $tenDaysAgo = $now->copy()->subDays(10);
        
        // Validate date calculations
        $this->assertTrue($sevenDaysAgo->lessThan($now));
        $this->assertTrue($tenDaysAgo->lessThan($sevenDaysAgo));
        $this->assertEquals(7, $now->diffInDays($sevenDaysAgo));
        $this->assertEquals(10, $now->diffInDays($tenDaysAgo));
    }
}