<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoticiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->is_admin ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'titulo' => [
                'required',
                'string',
                'max:255',
                'min:5',
                'regex:/^[a-zA-Z0-9\s\-\.\,\!\?\:\;\(\)áéíóúÁÉÍÓÚñÑüÜ]+$/' // Allow alphanumeric, spaces, and common punctuation
            ],
            'contenido' => [
                'required',
                'string',
                'max:' . config('uhtv.security.max_content_length', 250000),
                'min:50'
            ],
            'category_id' => 'required|exists:categories,id',
            'video_youtube' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !extract_youtube_id($value)) {
                        $fail('El enlace o ID de YouTube no es válido.');
                    }
                },
            ],
            'publicada' => 'boolean',
            'destacada_hero' => 'boolean',
            'es_investigacion' => 'boolean',
            'es_urgente' => 'boolean',
            'urgente_hasta' => 'nullable|date',
            'publicar_en' => 'nullable|date',
            'galeria_fotos' => 'nullable|array',
            'galeria_fotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:3072',
            'galeria_pie' => 'nullable|array',
            'galeria_pie.*' => 'nullable|string|max:255',
        ];

        // Enhanced image validation rules
        if ($this->isMethod('post')) {
            // For creating new news, image is required
            $rules['imagen'] = [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048', // 2MB max
                'min:1', // At least 1KB
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ];
        } else {
            // For updating news, image is optional
            $rules['imagen'] = [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
                'min:1',
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // Title validation messages
            'titulo.required' => 'El título es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 5 caracteres.',
            'titulo.max' => 'El título no puede exceder 255 caracteres.',
            'titulo.regex' => 'El título contiene caracteres no permitidos. Use solo letras, números y signos de puntuación básicos.',
            
            // Content validation messages
            'contenido.required' => 'El contenido es obligatorio.',
            'contenido.min' => 'El contenido debe tener al menos 50 caracteres.',
            'contenido.max' => 'El contenido no puede exceder ' . number_format(config('uhtv.security.max_content_length', 250000)) . ' caracteres.',
            
            // Category validation messages
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no es válida.',
            
            // Image validation messages
            'imagen.required' => 'La imagen es obligatoria para crear una noticia.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe ser de tipo: JPEG, PNG, JPG o WEBP.',
            'imagen.max' => 'La imagen no puede ser mayor a 2MB.',
            'imagen.min' => 'El archivo de imagen es demasiado pequeño.',
            'imagen.dimensions' => 'La imagen debe tener entre 100x100 y 4000x4000 píxeles.',
            
            // YouTube video validation messages
            'video_youtube.url' => 'La URL del video no es válida.',
            'video_youtube.regex' => 'La URL debe ser de YouTube válida (youtube.com o youtu.be).',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom content validation
            if ($this->has('contenido')) {
                $this->validateContentSafety($validator);
            }

            // Custom image validation
            if ($this->hasFile('imagen')) {
                $this->validateImageSafety($validator);
            }
        });
    }

    /**
     * Validate content for safety and quality
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateContentSafety($validator)
    {
        $content = $this->input('contenido');
        
        // Check for excessive HTML tags - more flexible limit for rich text content
        $htmlTagCount = substr_count($content, '<');
        $textContent = strip_tags($content);
        $textLength = strlen(trim($textContent));
        
        // Only flag as excessive if there are way too many tags relative to content
        // Allow up to 1 tag per 10 characters of text, with a minimum of 200 tags allowed
        $maxAllowedTags = max(200, intval($textLength / 10));
        
        if ($htmlTagCount > $maxAllowedTags && $htmlTagCount > 300) {
            $validator->errors()->add('contenido', 'El contenido tiene demasiadas etiquetas HTML para la cantidad de texto. Simplifique el formato.');
        }



        // Check content quality - ensure it's not just HTML tags
        if ($textLength < 30) {
            $validator->errors()->add('contenido', 'El contenido debe tener al menos 30 caracteres de texto real (sin contar etiquetas HTML).');
        }

        // Check for malformed HTML that could cause issues
        if ($htmlTagCount > 0) {
            $openTags = substr_count($content, '<');
            $closeTags = substr_count($content, '>');
            
            // Basic check for severely malformed HTML
            if (abs($openTags - $closeTags) > 10) {
                $validator->errors()->add('contenido', 'El contenido HTML parece estar mal formado. Verifique que las etiquetas estén correctamente cerradas.');
            }
        }
    }

    /**
     * Validate image file safety
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    private function validateImageSafety($validator)
    {
        $image = $this->file('imagen');
        
        if (!$image->isValid()) {
            $validator->errors()->add('imagen', 'El archivo de imagen está corrupto o no se subió correctamente.');
            return;
        }

        // Check if it's actually an image
        $imageInfo = @getimagesize($image->getPathname());
        if (!$imageInfo) {
            $validator->errors()->add('imagen', 'El archivo no es una imagen válida.');
            return;
        }

        // Check aspect ratio (not too narrow or too wide)
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $aspectRatio = $width / $height;
        
        if ($aspectRatio < 0.2 || $aspectRatio > 5) {
            $validator->errors()->add('imagen', 'La imagen tiene una proporción inusual. Use una imagen más cuadrada o rectangular normal.');
        }

        // Check file extension matches MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array($image->getMimeType(), $allowedMimes)) {
            $validator->errors()->add('imagen', 'El tipo de archivo no coincide con la extensión. Use una imagen válida.');
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'contenido' => 'contenido',
            'category_id' => 'categoría',
            'imagen' => 'imagen',
            'video_youtube' => 'video de YouTube',
            'publicada' => 'estado de publicación'
        ];
    }
}
