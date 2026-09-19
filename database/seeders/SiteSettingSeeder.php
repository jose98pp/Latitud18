<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Streaming
            ['key' => 'streaming_tv_active', 'value' => '1', 'group' => 'streaming'],
            ['key' => 'streaming_tv_title', 'value' => 'UHTV En Vivo — Transmisión Digital 24/7', 'group' => 'streaming'],
            ['key' => 'streaming_tv_youtube_id', 'value' => 'jfKfPfyJRdk', 'group' => 'streaming'],
            ['key' => 'streaming_radio_active', 'value' => '1', 'group' => 'streaming'],
            ['key' => 'streaming_radio_title', 'value' => 'Radio Latitud 18 FM — Señal Online', 'group' => 'streaming'],
            ['key' => 'streaming_radio_url', 'value' => 'https://stream.zeno.fm/f3wvbbqmdg8uv', 'group' => 'streaming'],

            // Redes Sociales
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/uhtvbolivia', 'group' => 'social'],
            ['key' => 'social_twitter', 'value' => 'https://x.com/UhtvBol', 'group' => 'social'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@UHTVBolivia', 'group' => 'social'],
            ['key' => 'social_tiktok', 'value' => 'https://tiktok.com/@uhtvbolivia', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/uhtvbolivia', 'group' => 'social'],
            ['key' => 'whatsapp_phone', 'value' => '+591 70000000', 'group' => 'social'],
            ['key' => 'whatsapp_message', 'value' => 'Hola, quiero enviar una denuncia o información a Latitud 18.', 'group' => 'social'],

            // Canal de YouTube y Galería de Portada
            ['key' => 'youtube_gallery_active', 'value' => '1', 'group' => 'youtube'],
            ['key' => 'youtube_channel_name', 'value' => 'Latitud 18 TV', 'group' => 'youtube'],
            ['key' => 'youtube_channel_handle', 'value' => '@Latitud18TV', 'group' => 'youtube'],
            ['key' => 'youtube_channel_url', 'value' => 'https://youtube.com/@UHTVBolivia', 'group' => 'youtube'],
            ['key' => 'youtube_channel_badge', 'value' => 'Canal Oficial • Cobertura 24/7', 'group' => 'youtube'],
            ['key' => 'youtube_gallery_videos', 'value' => '', 'group' => 'youtube'],

            // Datos de Contacto y Footer
            ['key' => 'site_name', 'value' => 'Latitud 18', 'group' => 'general'],
            ['key' => 'site_slogan', 'value' => 'Periodismo Independiente, Multimedia e Investigación', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'prensa@latitud18.bo', 'group' => 'general'],
            ['key' => 'contact_phone', 'value' => '+591 (2) 211-4500', 'group' => 'general'],
            ['key' => 'contact_address', 'value' => 'Av. 6 de Agosto #2455, Edificio Los Libertadores, La Paz, Bolivia', 'group' => 'general'],
            ['key' => 'footer_about', 'value' => 'Latitud 18 es un medio de comunicación multiplataforma independiente comprometido con la verdad, la investigación rigurosa y la inmediatez informativa en Bolivia y el mundo.', 'group' => 'general'],
            ['key' => 'copyright_text', 'value' => '© 2026 Latitud 18 / UHTV Bolivia. Todos los derechos reservados.', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}
