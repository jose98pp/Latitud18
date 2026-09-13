<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    /**
     * Upload an image for the rich text editor
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            // Check if file was uploaded
            if (!$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha seleccionado ningún archivo.'
                ], 400);
            }

            $file = $request->file('image');

            // Check if file upload was successful
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en la subida del archivo. Inténtelo de nuevo.'
                ], 400);
            }

            // Validate the uploaded file
            $request->validate([
                'image' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,gif,webp',
                    'max:5120' // 5MB in kilobytes
                ]
            ]);

            // Additional file size check (in case validation doesn't catch it)
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo es demasiado grande. Máximo 5MB permitido.'
                ], 413);
            }

            // Check available disk space
            $availableSpace = disk_free_space(storage_path('app/public'));
            if ($availableSpace !== false && $availableSpace < $file->getSize() * 2) {
                \Log::error('Insufficient disk space for image upload', [
                    'available_space' => $availableSpace,
                    'file_size' => $file->getSize()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio insuficiente en el servidor. Contacte al administrador.'
                ], 507);
            }

            // Generate a unique filename
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Store the file in storage/app/public/images directory
            $path = $file->storeAs('images', $filename, 'public');
            
            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el archivo en el servidor.'
                ], 500);
            }

            // Verify the file was actually stored
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al verificar el archivo guardado.'
                ], 500);
            }
            
            // Generate the public URL
            $url = Storage::url($path);
            
            \Log::info('Image uploaded successfully', [
                'filename' => $filename,
                'path' => $path,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'filename' => $filename,
                'message' => 'Imagen subida correctamente'
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $message = 'Error de validación';
            
            // Provide more specific error messages
            if (isset($errors['image'])) {
                $firstError = $errors['image'][0];
                if (str_contains($firstError, 'mimes')) {
                    $message = 'Tipo de archivo no válido. Solo se permiten JPG, PNG, GIF y WebP.';
                } elseif (str_contains($firstError, 'max')) {
                    $message = 'El archivo es demasiado grande. Máximo 5MB permitido.';
                } elseif (str_contains($firstError, 'image')) {
                    $message = 'El archivo debe ser una imagen válida.';
                } else {
                    $message = $firstError;
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Image upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al subir la imagen. Inténtelo de nuevo.'
            ], 500);
        }
    }
}