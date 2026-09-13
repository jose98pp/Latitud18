<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class VerifyStorageSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:verify 
                            {--fix : Intentar reparar problemas encontrados}
                            {--create-dirs : Crear directorios faltantes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar y reparar la configuración de storage para imágenes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando configuración de storage...');
        
        $issues = [];
        $fix = $this->option('fix');
        $createDirs = $this->option('create-dirs');

        // Verificar enlace simbólico
        $issues = array_merge($issues, $this->checkSymbolicLink($fix));

        // Verificar permisos
        $issues = array_merge($issues, $this->checkPermissions($fix));

        // Verificar estructura de directorios
        $issues = array_merge($issues, $this->checkDirectoryStructure($createDirs));

        // Verificar imágenes existentes
        $issues = array_merge($issues, $this->checkExistingImages());

        // Mostrar resumen
        $this->showSummary($issues);
    }

    /**
     * Verificar enlace simbólico
     */
    private function checkSymbolicLink(bool $fix): array
    {
        $issues = [];
        $publicStoragePath = public_path('storage');
        $storagePath = storage_path('app/public');

        $this->info('📁 Verificando enlace simbólico...');

        if (!File::exists($publicStoragePath)) {
            $issues[] = [
                'type' => 'error',
                'message' => 'El enlace simbólico public/storage no existe'
            ];

            if ($fix) {
                try {
                    if (function_exists('symlink')) {
                        symlink($storagePath, $publicStoragePath);
                        $this->info('✅ Enlace simbólico creado');
                        $issues[count($issues) - 1]['fixed'] = true;
                    } else {
                        $issues[] = [
                            'type' => 'error',
                            'message' => 'La función symlink no está disponible'
                        ];
                    }
                } catch (\Exception $e) {
                    $issues[] = [
                        'type' => 'error',
                        'message' => 'Error al crear enlace simbólico: ' . $e->getMessage()
                    ];
                }
            }
        } elseif (!is_link($publicStoragePath)) {
            $issues[] = [
                'type' => 'warning',
                'message' => 'public/storage existe pero no es un enlace simbólico'
            ];
        } else {
            $this->info('✅ Enlace simbólico correcto');
        }

        return $issues;
    }

    /**
     * Verificar permisos
     */
    private function checkPermissions(bool $fix): array
    {
        $issues = [];
        $storagePath = storage_path('app/public');

        $this->info('🔐 Verificando permisos...');

        if (!is_writable($storagePath)) {
            $issues[] = [
                'type' => 'error',
                'message' => 'El directorio storage/app/public no tiene permisos de escritura'
            ];

            if ($fix) {
                try {
                    chmod($storagePath, 0755);
                    $this->info('✅ Permisos corregidos');
                    $issues[count($issues) - 1]['fixed'] = true;
                } catch (\Exception $e) {
                    $issues[] = [
                        'type' => 'error',
                        'message' => 'Error al corregir permisos: ' . $e->getMessage()
                    ];
                }
            }
        } else {
            $this->info('✅ Permisos correctos');
        }

        return $issues;
    }

    /**
     * Verificar estructura de directorios
     */
    private function checkDirectoryStructure(bool $createDirs): array
    {
        $issues = [];
        $requiredDirs = [
            'noticias',
            'backups',
            'backups/images',
            'archive',
            'archive/orphan_images'
        ];

        $this->info('📂 Verificando estructura de directorios...');

        foreach ($requiredDirs as $dir) {
            if (!Storage::disk('public')->exists($dir)) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Directorio faltante: storage/app/public/{$dir}"
                ];

                if ($createDirs) {
                    try {
                        Storage::disk('public')->makeDirectory($dir);
                        $this->info("✅ Directorio creado: {$dir}");
                        $issues[count($issues) - 1]['fixed'] = true;
                    } catch (\Exception $e) {
                        $issues[] = [
                            'type' => 'error',
                            'message' => "Error al crear directorio {$dir}: " . $e->getMessage()
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Verificar imágenes existentes
     */
    private function checkExistingImages(): array
    {
        $issues = [];

        $this->info('🖼️  Verificando imágenes existentes...');

        try {
            $noticias = \App\Models\Noticia::whereNotNull('imagen')
                ->where('imagen', '!=', '')
                ->get();

            $totalImages = $noticias->count();
            $missingImages = 0;
            $validImages = 0;

            foreach ($noticias as $noticia) {
                if (!Storage::disk('public')->exists($noticia->imagen)) {
                    $missingImages++;
                } else {
                    $validImages++;
                }
            }

            $this->table([
                'Métrica', 'Cantidad'
            ], [
                ['Total de referencias', $totalImages],
                ['Imágenes válidas', $validImages],
                ['Imágenes faltantes', $missingImages],
            ]);

            if ($missingImages > 0) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "{$missingImages} imágenes referenciadas no se encontraron en storage"
                ];
            }

        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'error',
                'message' => 'Error al verificar imágenes: ' . $e->getMessage()
            ];
        }

        return $issues;
    }

    /**
     * Mostrar resumen
     */
    private function showSummary(array $issues): void
    {
        $this->info('📊 Resumen de verificación:');

        $errors = collect($issues)->where('type', 'error')->count();
        $warnings = collect($issues)->where('type', 'warning')->count();
        $fixed = collect($issues)->where('fixed', true)->count();

        $this->table([
            'Tipo', 'Cantidad'
        ], [
            ['❌ Errores', $errors],
            ['⚠️  Advertencias', $warnings],
            ['✅ Reparados', $fixed],
        ]);

        if ($errors > 0 || $warnings > 0) {
            $this->info('🔧 Problemas encontrados:');
            foreach ($issues as $issue) {
                $icon = $issue['type'] === 'error' ? '❌' : '⚠️';
                $status = isset($issue['fixed']) && $issue['fixed'] ? ' (REPARADO)' : '';
                $this->line("  {$icon} {$issue['message']}{$status}");
            }

            if (!$this->option('fix') && !$this->option('create-dirs')) {
                $this->info('💡 Ejecute con --fix --create-dirs para intentar reparar automáticamente');
            }
        } else {
            $this->info('🎉 ¡Configuración de storage correcta!');
        }
    }
}