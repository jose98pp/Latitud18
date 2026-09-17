<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el panel de configuración general y streaming
     */
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();

        return view('admin.configuracion.index', compact('settings'));
    }

    /**
     * Guarda las configuraciones
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // Mapeo de grupos
        $streamingKeys = [
            'streaming_tv_active',
            'streaming_tv_title',
            'streaming_tv_youtube_id',
            'streaming_radio_active',
            'streaming_radio_title',
            'streaming_radio_url',
        ];

        $socialKeys = [
            'social_facebook',
            'social_twitter',
            'social_youtube',
            'social_tiktok',
            'social_instagram',
            'whatsapp_phone',
            'whatsapp_message',
        ];

        // Manejar switches booleanos no enviados si están desactivados
        if (!isset($data['streaming_tv_active'])) {
            $data['streaming_tv_active'] = '0';
        }
        if (!isset($data['streaming_radio_active'])) {
            $data['streaming_radio_active'] = '0';
        }

        // Si el usuario pega una URL completa de YouTube en el campo ID, extraer automáticamente el ID
        if (!empty($data['streaming_tv_youtube_id'])) {
            $cleanedYtId = extract_youtube_id($data['streaming_tv_youtube_id']);
            if ($cleanedYtId) {
                $data['streaming_tv_youtube_id'] = $cleanedYtId;
            }
        }

        foreach ($data as $key => $value) {
            $group = 'general';
            if (in_array($key, $streamingKeys)) {
                $group = 'streaming';
            } elseif (in_array($key, $socialKeys)) {
                $group = 'social';
            }

            SiteSetting::set($key, $value ?? '', $group);
        }

        return redirect()->route('admin.configuracion.index')->with('success', 'Configuración actualizada exitosamente.');
    }
}
