<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Validator;

class NewsletterPublicController extends Controller
{
    /**
     * Registra un nuevo suscriptor al boletín vía AJAX
     */
    public function subscribe(Request $request)
    {
        $data = $request->isJson() ? $request->json()->all() : $request->all();

        $validator = Validator::make($data, [
            'email' => 'required|email|max:255',
            'nombre' => 'nullable|string|max:100',
            'origen' => 'nullable|string|max:100',
        ], [
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $nombre = $request->filled('nombre') ? trim(strip_tags($request->input('nombre'))) : null;
        $ip = $request->ip();

        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber) {
            if (!$subscriber->activo) {
                $subscriber->update(['activo' => true]);
                return response()->json([
                    'success' => true,
                    'message' => '¡Tu suscripción ha sido reactivada con éxito!',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ya te encuentras suscrito a nuestro boletín de noticias.',
            ]);
        }

        $origen = isset($data['origen']) ? trim(strip_tags($data['origen'])) : 'Portal';

        NewsletterSubscriber::create([
            'email' => $email,
            'nombre' => $nombre,
            'origen' => $origen,
            'activo' => true,
            'ip_address' => $ip,
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Gracias por suscribirte! Recibirás nuestras noticias destacadas.',
        ]);
    }
}
