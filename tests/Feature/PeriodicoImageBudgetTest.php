<?php

namespace Tests\Feature;

use App\Services\PeriodicoPdfService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeriodicoImageBudgetTest extends TestCase
{
    public function test_large_photograph_is_resized_for_pdf_without_touching_original(): void
    {
        Storage::fake('public');
        $path = Storage::disk('public')->path('photo.jpg');
        $image = imagecreatetruecolor(3000, 1500);
        imagejpeg($image, $path, 85); imagedestroy($image);
        $hash = hash_file('sha256', $path);
        $service = app(PeriodicoPdfService::class);
        $optimized = $service->imagenParaPdf('/storage/photo.jpg');
        try {
            $this->assertNotSame($path, $optimized);
            $this->assertSame([2400,1200], array_slice(getimagesize($optimized), 0, 2));
            $this->assertSame($hash, hash_file('sha256', $path));
            $this->assertSame($optimized, $service->imagenParaPdf('/storage/photo.jpg'));
        } finally { @unlink($optimized); }
    }

    public function test_large_editions_use_smaller_derivatives_and_keep_all_48_pages(): void
    {
        Storage::fake('public');
        $image = imagecreatetruecolor(3000, 1500);
        $frames = [];
        for ($i=1; $i<=17; $i++) {
            imagejpeg($image, Storage::disk('public')->path('photo-'.$i.'.jpg'), 85);
            $frames[] = ['id'=>'photo-'.$i, 'type'=>'image', 'x'=>20, 'y'=>20, 'w'=>100, 'h'=>60, 'src'=>'/storage/photo-'.$i.'.jpg'];
        }
        imagedestroy($image);
        $pages = [];
        for ($i=1; $i<=48; $i++) $pages[] = ['numero'=>$i, 'ancho'=>720, 'alto'=>1106,
            'frames'=>$i===1 ? $frames : [['id'=>'text-'.$i,'type'=>'text','x'=>30,'y'=>30,'w'=>500,'h'=>100,'content'=>'Página de control '.$i]]];
        $service = app(PeriodicoPdfService::class);
        $pdf = $service->contenido(['id'=>'stress-regression','paginas'=>$pages]);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertSame(48, preg_match_all('#/Type\\s*/Page\\b#', $pdf));
        $optimized = $service->imagenParaPdf('/storage/photo-1.jpg');
        try { $this->assertSame([1600,800], array_slice(getimagesize($optimized),0,2)); }
        finally { @unlink($optimized); }
    }

    public function test_oversized_image_is_rejected_on_every_attempt(): void
    {
        Storage::fake('public');
        $path = Storage::disk('public')->path('photo.jpg');
        $image = imagecreatetruecolor(100,100); imagejpeg($image,$path); imagedestroy($image);
        config(['periodico.pdf_image_max_pixels'=>9999]);
        $service = app(PeriodicoPdfService::class);
        for ($i=0; $i<2; $i++) {
            try { $service->imagenParaPdf('/storage/photo.jpg'); $this->fail('An oversized image must be rejected.'); }
            catch (\RuntimeException $e) { $this->assertStringContainsString('megapíxeles', $e->getMessage()); }
        }
    }
}
