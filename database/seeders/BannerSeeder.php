<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run()
    {
        // Banner Principal (Header)
        Banner::create([
            'title' => 'Banner Principal',
            'image_path' => 'images/banner.png',
            'link' => '#',
            'location' => 'portada_top',
            'position' => 1,
            'active' => true,
        ]);

        // Banner Sidebar
        Banner::create([
            'title' => 'Publicidad Sidebar',
            'image_path' => 'images/publicita-left.jpeg',
            'link' => '#',
            'location' => 'sidebar',
            'position' => 1,
            'active' => true,
        ]);

        // Banner Footer (Radio Betania)
        Banner::create([
            'title' => 'Radio Betania',
            'image_path' => 'images/betania.jpg',
            'link' => 'https://radiobetania.com/',
            'location' => 'footer',
            'position' => 1,
            'active' => true,
        ]);
    }
}
