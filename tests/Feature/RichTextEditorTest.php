<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Noticia;
use App\Models\User;
use App\Services\ContentSanitizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RichTextEditorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $category;
    protected $sanitizationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);
        
        // Create test category
        $this->category = Category::create([
            'name' => 'Test Category',
            'descripcion' => null
        ]);

        // Initialize sanitization service
        $this->sanitizationService = new ContentSanitizationService();
        
        // Setup storage for testing
        Storage::fake('public');
    }

    /** @test */
    public function test_noticias_create_view_loads_successfully()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.noticias.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.noticias.create');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function test_noticias_edit_view_loads_successfully()
    {
        $noticia = Noticia::create([
            'titulo' => 'Test News',
            'contenido' => '<p>Test content</p>',
            'category_id' => $this->category->id,
            'publicada' => false
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.noticias.edit', $noticia->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.noticias.edit');
        $response->assertViewHas(['noticia', 'categories']);
    }

    /** @test */
    public function test_create_noticia_with_rich_content()
    {
        $richContent = '<p>This is <strong>bold</strong> and <em>italic</em> text.</p><ul><li>Item 1</li><li>Item 2</li></ul>';
        
        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Test News with Rich Content',
                'contenido' => $richContent,
                'category_id' => $this->category->id,
                'publicada' => true,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('noticias', [
            'titulo' => 'Test News with Rich Content',
            'category_id' => $this->category->id,
            'publicada' => true
        ]);

        $noticia = Noticia::where('titulo', 'Test News with Rich Content')->first();
        $this->assertNotNull($noticia);
        $this->assertStringContainsString('<strong>bold</strong>', $noticia->contenido);
        $this->assertStringContainsString('<em>italic</em>', $noticia->contenido);
    }

    /** @test */
    public function test_update_noticia_with_rich_content()
    {
        $noticia = Noticia::create([
            'titulo' => 'Original Title',
            'contenido' => '<p>Original content</p>',
            'category_id' => $this->category->id,
            'publicada' => false
        ]);

        $updatedContent = '<p>Updated content with <strong>formatting</strong></p>';

        $response = $this->actingAs($this->user)
            ->put(route('admin.noticias.update', $noticia->id), [
                'titulo' => 'Updated Title',
                'contenido' => $updatedContent,
                'category_id' => $this->category->id,
                'publicada' => true
            ]);

        $response->assertRedirect(route('admin.noticias.index'));
        $response->assertSessionHas('success');

        $noticia->refresh();
        $this->assertEquals('Updated Title', $noticia->titulo);
        $this->assertStringContainsString('<strong>formatting</strong>', $noticia->contenido);
        $this->assertEquals(1, $noticia->publicada);
    }

    /** @test */
    public function test_create_categoria_with_rich_description()
    {
        $richDescription = '<p>Category description with <strong>formatting</strong></p>';

        $response = $this->actingAs($this->user)
            ->post(route('admin.categorias.store'), [
                'name' => 'Rich Category',
                'descripcion' => $richDescription
            ]);

        $response->assertRedirect(route('admin.categorias.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Rich Category'
        ]);

        $category = Category::where('name', 'Rich Category')->first();
        $this->assertNotNull($category);
        $this->assertStringContainsString('<strong>formatting</strong>', $category->descripcion);
    }

    /** @test */
    public function test_update_categoria_with_rich_description()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'descripcion' => '<p>Original description</p>'
        ]);

        $updatedDescription = '<p>Updated description with <em>emphasis</em></p>';

        $response = $this->actingAs($this->user)
            ->put(route('admin.categorias.update', $category->id), [
                'name' => 'Updated Category',
                'descripcion' => $updatedDescription
            ]);

        $response->assertRedirect(route('admin.categorias.index'));
        $response->assertSessionHas('success');

        $category->refresh();
        $this->assertEquals('Updated Category', $category->name);
        $this->assertStringContainsString('<em>emphasis</em>', $category->descripcion);
    }

    /** @test */
    public function test_content_sanitization_removes_dangerous_scripts()
    {
        $dangerousContent = '<p>Safe content</p><script>alert("xss")</script><p onclick="alert(\'click\')">Click me</p>';

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Test XSS Protection',
                'contenido' => $dangerousContent,
                'category_id' => $this->category->id,
                'publicada' => false,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));

        $noticia = Noticia::where('titulo', 'Test XSS Protection')->first();
        $this->assertNotNull($noticia);
        
        // Should not contain script tags
        $this->assertStringNotContainsString('<script>', $noticia->contenido);
        $this->assertStringNotContainsString('alert(', $noticia->contenido);
        $this->assertStringNotContainsString('onclick=', $noticia->contenido);
        
        // Should still contain safe content
        $this->assertStringContainsString('<p>Safe content</p>', $noticia->contenido);
    }

    /** @test */
    public function test_content_length_validation()
    {
        $longContent = str_repeat('<p>Very long content. </p>', 2000); // Exceeds limit

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Test Long Content',
                'contenido' => $longContent,
                'category_id' => $this->category->id,
                'publicada' => false
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('contenido');
        
        $this->assertDatabaseMissing('noticias', [
            'titulo' => 'Test Long Content'
        ]);
    }

    /** @test */
    public function test_image_upload_api_success()
    {
        // Create a fake image file without using GD
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg'); // 1MB

        $response = $this->actingAs($this->user)
            ->post('/api/upload-image', [
                'image' => $file
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('filename', $responseData);
        
        // Check if file was stored
        $filename = $responseData['filename'];
        Storage::disk('public')->assertExists('images/' . $filename);
    }

    /** @test */
    public function test_image_upload_api_file_too_large()
    {
        // Create a fake large image file
        $file = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg'); // 6MB

        $response = $this->actingAs($this->user)
            ->post('/api/upload-image', [
                'image' => $file
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function test_image_upload_api_invalid_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($this->user)
            ->post('/api/upload-image', [
                'image' => $file
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function test_image_upload_api_no_file()
    {
        $response = $this->actingAs($this->user)
            ->post('/api/upload-image', []);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'No se ha seleccionado ningún archivo.'
        ]);
    }

    /** @test */
    public function test_content_sanitization_service_basic_functionality()
    {
        $content = '<p>Normal content with <strong>bold</strong> text</p>';
        $sanitized = $this->sanitizationService->sanitizeContent($content);
        
        $this->assertEquals($content, $sanitized);
    }

    /** @test */
    public function test_content_sanitization_service_removes_scripts()
    {
        $content = '<p>Safe content</p><script>alert("xss")</script>';
        $sanitized = $this->sanitizationService->sanitizeContent($content);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('<p>Safe content</p>', $sanitized);
    }

    /** @test */
    public function test_content_sanitization_service_removes_event_handlers()
    {
        $content = '<p onclick="alert(\'click\')">Click me</p>';
        $sanitized = $this->sanitizationService->sanitizeContent($content);
        
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringContainsString('<p>Click me</p>', $sanitized);
    }

    /** @test */
    public function test_content_length_validation_service()
    {
        $shortContent = 'Short';
        $error = $this->sanitizationService->validateContentLength($shortContent);
        $this->assertEquals('El contenido debe tener al menos 10 caracteres.', $error);

        $normalContent = 'This is a normal length content that should pass validation.';
        $error = $this->sanitizationService->validateContentLength($normalContent);
        $this->assertNull($error);

        $longContent = str_repeat('Very long content. ', 3000);
        $error = $this->sanitizationService->validateContentLength($longContent, 50000);
        $this->assertStringContainsString('excede el límite máximo', $error);
    }

    /** @test */
    public function test_dangerous_content_detection()
    {
        $dangerousContent = '<script>alert("xss")</script><p onclick="alert()">Click</p>';
        $warnings = $this->sanitizationService->hasDangerousContent($dangerousContent);
        
        $this->assertNotEmpty($warnings);
        $this->assertCount(2, $warnings);
    }

    /** @test */
    public function test_noticias_form_validation_requires_content()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Test News',
                'contenido' => '', // Empty content
                'category_id' => $this->category->id,
                'publicada' => false
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('contenido');
        
        $this->assertDatabaseMissing('noticias', [
            'titulo' => 'Test News'
        ]);
    }

    /** @test */
    public function test_categorias_can_have_empty_description()
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.categorias.store'), [
                'name' => 'Category Without Description',
                'descripcion' => ''
            ]);

        $response->assertRedirect(route('admin.categorias.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Category Without Description',
            'descripcion' => null
        ]);
    }

    /** @test */
    public function test_rich_content_preserves_formatting_on_edit()
    {
        $originalContent = '<h2>Heading</h2><p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p><ul><li>List item 1</li><li>List item 2</li></ul>';
        
        $noticia = Noticia::create([
            'titulo' => 'Formatted News',
            'contenido' => $originalContent,
            'category_id' => $this->category->id,
            'publicada' => false
        ]);

        // Simulate editing without changing content
        $response = $this->actingAs($this->user)
            ->put(route('admin.noticias.update', $noticia->id), [
                'titulo' => 'Formatted News Updated',
                'contenido' => $originalContent,
                'category_id' => $this->category->id,
                'publicada' => false
            ]);

        $response->assertRedirect(route('admin.noticias.index'));
        
        $noticia->refresh();
        $this->assertStringContainsString('<h2>Heading</h2>', $noticia->contenido);
        $this->assertStringContainsString('<strong>bold</strong>', $noticia->contenido);
        $this->assertStringContainsString('<em>italic</em>', $noticia->contenido);
        $this->assertStringContainsString('<ul><li>', $noticia->contenido);
    }

    /** @test */
    public function test_editor_views_contain_required_scripts_and_elements()
    {
        // Test noticias create view
        $response = $this->actingAs($this->user)
            ->get(route('admin.noticias.create'));

        $response->assertStatus(200);
        $response->assertSee('editor-container', false);
        $response->assertSee('contenido-hidden', false);
        $response->assertSee('rich-text-editor-init.js', false);
        $response->assertSee('RichTextEditorManager', false);

        // Test noticias edit view
        $noticia = Noticia::create([
            'titulo' => 'Test News',
            'contenido' => '<p>Test content</p>',
            'category_id' => $this->category->id,
            'publicada' => false
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.noticias.edit', $noticia->id));

        $response->assertStatus(200);
        $response->assertSee('editor-container', false);
        $response->assertSee('contenido-hidden', false);
        $response->assertSee('rich-text-editor-init.js', false);

        // Test categorias create view
        $response = $this->actingAs($this->user)
            ->get(route('admin.categorias.create'));

        $response->assertStatus(200);
        $response->assertSee('editor-container', false);
        $response->assertSee('descripcion-hidden', false);
        $response->assertSee('rich-text-editor-init.js', false);

        // Test categorias edit view
        $response = $this->actingAs($this->user)
            ->get(route('admin.categorias.edit', $this->category->id));

        $response->assertStatus(200);
        $response->assertSee('editor-container', false);
        $response->assertSee('descripcion-hidden', false);
        $response->assertSee('rich-text-editor-init.js', false);
    }

    /** @test */
    public function test_complex_html_content_handling()
    {
        $complexContent = '
            <h1>Main Title</h1>
            <h2>Subtitle</h2>
            <p>This is a paragraph with <strong>bold</strong>, <em>italic</em>, and <u>underlined</u> text.</p>
            <p style="color: red;">Colored text paragraph</p>
            <ul>
                <li>First item</li>
                <li>Second item with <strong>formatting</strong></li>
                <li>Third item</li>
            </ul>
            <ol>
                <li>Numbered item 1</li>
                <li>Numbered item 2</li>
            </ol>
            <blockquote>This is a quote</blockquote>
            <p>Text with <a href="https://example.com">a link</a></p>
        ';

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Complex HTML Test',
                'contenido' => $complexContent,
                'category_id' => $this->category->id,
                'publicada' => false,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));

        $noticia = Noticia::where('titulo', 'Complex HTML Test')->first();
        $this->assertNotNull($noticia);
        
        // Verify various HTML elements are preserved
        $this->assertStringContainsString('<h1>Main Title</h1>', $noticia->contenido);
        $this->assertStringContainsString('<h2>Subtitle</h2>', $noticia->contenido);
        $this->assertStringContainsString('<strong>bold</strong>', $noticia->contenido);
        $this->assertStringContainsString('<em>italic</em>', $noticia->contenido);
        $this->assertStringContainsString('<ul>', $noticia->contenido);
        $this->assertStringContainsString('<ol>', $noticia->contenido);
        $this->assertStringContainsString('<blockquote>', $noticia->contenido);
    }

    /** @test */
    public function test_image_insertion_in_content()
    {
        // Create a fake image file
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

        // Upload image via API
        $response = $this->actingAs($this->user)
            ->post('/api/upload-image', [
                'image' => $file
            ]);

        $response->assertStatus(200);
        $responseData = $response->json();
        $imageUrl = $responseData['url'];

        // Create content with the uploaded image
        $contentWithImage = '<p>Text before image</p><img src="' . $imageUrl . '" alt="Test image"><p>Text after image</p>';

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'News with Image',
                'contenido' => $contentWithImage,
                'category_id' => $this->category->id,
                'publicada' => false,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));

        $noticia = Noticia::where('titulo', 'News with Image')->first();
        $this->assertNotNull($noticia);
        $this->assertStringContainsString('<img src="', $noticia->contenido);
        $this->assertStringContainsString($imageUrl, $noticia->contenido);
    }

    /** @test */
    public function test_content_with_special_characters()
    {
        $specialContent = '<p>Content with special characters: áéíóú ñ ¿¡ & < > " \' €</p>';

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Special Characters Test',
                'contenido' => $specialContent,
                'category_id' => $this->category->id,
                'publicada' => false,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));

        $noticia = Noticia::where('titulo', 'Special Characters Test')->first();
        $this->assertNotNull($noticia);
        
        // Verify special characters are preserved
        $this->assertStringContainsString('áéíóú', $noticia->contenido);
        $this->assertStringContainsString('ñ', $noticia->contenido);
        $this->assertStringContainsString('¿¡', $noticia->contenido);
    }

    /** @test */
    public function test_empty_content_handling()
    {
        // Test with completely empty content
        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Empty Content Test',
                'contenido' => '',
                'category_id' => $this->category->id,
                'publicada' => false
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('contenido');

        // Test with only HTML tags but no text content
        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Empty HTML Test',
                'contenido' => '<p></p><div></div>',
                'category_id' => $this->category->id,
                'publicada' => false
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('contenido');
    }

    /** @test */
    public function test_large_content_handling()
    {
        // Create content that's within limits
        $normalContent = str_repeat('<p>Normal content paragraph. </p>', 100); // About 2,700 chars

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Normal Size Content',
                'contenido' => $normalContent,
                'category_id' => $this->category->id,
                'publicada' => false,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('noticias', [
            'titulo' => 'Normal Size Content'
        ]);
    }

    /** @test */
    public function test_categorias_with_rich_content_workflow()
    {
        // Create category with rich description
        $richDescription = '<h3>Category Description</h3><p>This category contains <strong>important</strong> news about <em>technology</em>.</p><ul><li>Feature 1</li><li>Feature 2</li></ul>';

        $response = $this->actingAs($this->user)
            ->post(route('admin.categorias.store'), [
                'name' => 'Tech Category',
                'descripcion' => $richDescription
            ]);

        $response->assertRedirect(route('admin.categorias.index'));
        $response->assertSessionHas('success');

        $category = Category::where('name', 'Tech Category')->first();
        $this->assertNotNull($category);
        $this->assertStringContainsString('<h3>Category Description</h3>', $category->descripcion);
        $this->assertStringContainsString('<strong>important</strong>', $category->descripcion);

        // Update category description
        $updatedDescription = '<h3>Updated Description</h3><p>Updated content with <u>underlined</u> text.</p>';

        $response = $this->actingAs($this->user)
            ->put(route('admin.categorias.update', $category->id), [
                'name' => 'Updated Tech Category',
                'descripcion' => $updatedDescription
            ]);

        $response->assertRedirect(route('admin.categorias.index'));
        $response->assertSessionHas('success');

        $category->refresh();
        $this->assertEquals('Updated Tech Category', $category->name);
        $this->assertStringContainsString('<h3>Updated Description</h3>', $category->descripcion);
        $this->assertStringContainsString('<u>underlined</u>', $category->descripcion);
    }

    /** @test */
    public function test_concurrent_image_uploads()
    {
        // Test multiple image uploads don't interfere with each other
        $file1 = UploadedFile::fake()->create('test1.jpg', 1024, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('test2.png', 1024, 'image/png');

        $response1 = $this->actingAs($this->user)
            ->post('/api/upload-image', ['image' => $file1]);

        $response2 = $this->actingAs($this->user)
            ->post('/api/upload-image', ['image' => $file2]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $data1 = $response1->json();
        $data2 = $response2->json();

        $this->assertTrue($data1['success']);
        $this->assertTrue($data2['success']);
        $this->assertNotEquals($data1['filename'], $data2['filename']);

        // Verify both files exist
        Storage::disk('public')->assertExists('images/' . $data1['filename']);
        Storage::disk('public')->assertExists('images/' . $data2['filename']);
    }

    /** @test */
    public function test_content_sanitization_comprehensive()
    {
        $maliciousContent = '
            <p>Safe content</p>
            <script>alert("xss")</script>
            <iframe src="javascript:alert(1)"></iframe>
            <img src="x" onerror="alert(1)">
            <div onclick="alert(1)">Click me</div>
            <a href="javascript:alert(1)">Link</a>
            <form action="malicious.php"><input type="submit"></form>
            <object data="malicious.swf"></object>
            <embed src="malicious.swf">
            <link rel="stylesheet" href="malicious.css">
            <style>body { background: url("javascript:alert(1)"); }</style>
        ';

        $response = $this->actingAs($this->user)
            ->post(route('admin.noticias.store'), [
                'titulo' => 'Comprehensive XSS Test',
                'contenido' => $maliciousContent,
                'category_id' => $this->category->id,
                'publicada' => false,
                'imagen' => new UploadedFile(public_path('images/Logo.jpg'), 'Logo.jpg', 'image/jpeg', null, true)
            ]);

        $response->assertRedirect(route('admin.noticias.index'));

        $noticia = Noticia::where('titulo', 'Comprehensive XSS Test')->first();
        $this->assertNotNull($noticia);

        // Should not contain any dangerous elements
        $this->assertStringNotContainsString('<script>', $noticia->contenido);
        $this->assertStringNotContainsString('<iframe>', $noticia->contenido);
        $this->assertStringNotContainsString('onerror=', $noticia->contenido);
        $this->assertStringNotContainsString('onclick=', $noticia->contenido);
        $this->assertStringNotContainsString('javascript:', $noticia->contenido);
        $this->assertStringNotContainsString('<form>', $noticia->contenido);
        $this->assertStringNotContainsString('<object>', $noticia->contenido);
        $this->assertStringNotContainsString('<embed>', $noticia->contenido);
        $this->assertStringNotContainsString('<link>', $noticia->contenido);
        $this->assertStringNotContainsString('<style>', $noticia->contenido);

        // Should still contain safe content
        $this->assertStringContainsString('<p>Safe content</p>', $noticia->contenido);
    }
}