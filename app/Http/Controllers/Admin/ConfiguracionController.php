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

        $youtubeGalleryKeys = [
            'youtube_gallery_active',
            'youtube_channel_name',
            'youtube_channel_handle',
            'youtube_channel_url',
            'youtube_channel_badge',
            'youtube_gallery_videos',
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
        if (!isset($data['youtube_gallery_active'])) {
            $data['youtube_gallery_active'] = '0';
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
            } elseif (in_array($key, $youtubeGalleryKeys)) {
                $group = 'youtube';
            } elseif (in_array($key, $socialKeys)) {
                $group = 'social';
            }

            SiteSetting::set($key, $value ?? '', $group);
        }

        return redirect()->route('admin.configuracion.index')->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Alterna rápidamente el estado de la transmisión en vivo (ON/OFF)
     */
    public function toggleLive(Request $request)
    {
        $current = SiteSetting::get('streaming_tv_active', '0');
        
        if ($request->has('state')) {
            $new = $request->input('state') == '1' ? '1' : '0';
        } else {
            $new = ($current == '1') ? '0' : '1';
        }

        SiteSetting::set('streaming_tv_active', $new, 'streaming');

        $msg = ($new == '1')
            ? '🔴 Transmisión EN VIVO activada. El botón ya se muestra a los visitantes del portal.'
            : '⚪ Transmisión finalizada. El botón EN VIVO se ha ocultado del portal.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'active' => $new == '1',
                'message' => $msg
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Actualiza y activa la transmisión en vivo rápidamente desde el dashboard
     */
    public function quickUpdateLive(Request $request)
    {
        $request->validate([
            'streaming_tv_youtube_id' => 'nullable|string|max:255',
            'streaming_tv_title' => 'nullable|string|max:255',
        ]);

        if ($request->filled('streaming_tv_youtube_id')) {
            $cleanedYtId = extract_youtube_id($request->input('streaming_tv_youtube_id'));
            SiteSetting::set('streaming_tv_youtube_id', $cleanedYtId ?: $request->input('streaming_tv_youtube_id'), 'streaming');
        }

        if ($request->filled('streaming_tv_title')) {
            SiteSetting::set('streaming_tv_title', $request->input('streaming_tv_title'), 'streaming');
        }

        // Si se envió el flag para activar
        $activate = $request->input('activate', '1') == '1' ? '1' : '0';
        SiteSetting::set('streaming_tv_active', $activate, 'streaming');

        $msg = ($activate == '1')
            ? '🔴 Transmisión EN VIVO configurada y activada al aire.'
            : 'Configuración de transmisión guardada (Fuera del aire).';

        return back()->with('success', $msg);
    }
}
