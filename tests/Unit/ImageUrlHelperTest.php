<?php

namespace Tests\Unit;

use App\Helpers\ImageUrlHelper;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUrlHelperTest extends TestCase
{
    public function test_it_resolves_images_moved_into_category_folders(): void
    {
        Storage::disk('public')->put('noticias/deportes/test-image.jpg', 'fake-image');

        $url = ImageUrlHelper::getImageUrl('noticias/test-image.jpg');

        $this->assertStringContainsString('storage/noticias/deportes/test-image.jpg', $url);

        Storage::disk('public')->delete('noticias/deportes/test-image.jpg');
    }
}
