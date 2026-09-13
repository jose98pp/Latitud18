<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class AssetValidationTest extends TestCase
{
    /**
     * Test that all required CSS files exist in the resources directory
     */
    public function test_required_css_files_exist(): void
    {
        $requiredCssFiles = [
            'resources/css/app.css',
            'resources/css/browser-compatibility.css',
            'resources/css/dark-mode.css',
            'resources/css/show-dark-mode.css'
        ];

        foreach ($requiredCssFiles as $cssFile) {
            $fullPath = base_path($cssFile);
            $this->assertFileExists(
                $fullPath,
                "Required CSS file does not exist: {$cssFile}"
            );
        }
    }

    /**
     * Test that CSS files are not empty
     */
    public function test_css_files_are_not_empty(): void
    {
        $cssFiles = [
            'resources/css/app.css',
            'resources/css/browser-compatibility.css',
            'resources/css/dark-mode.css',
            'resources/css/show-dark-mode.css'
        ];

        foreach ($cssFiles as $cssFile) {
            $fullPath = base_path($cssFile);
            
            if (File::exists($fullPath)) {
                $content = File::get($fullPath);
                $this->assertNotEmpty(
                    trim($content),
                    "CSS file is empty: {$cssFile}"
                );
            }
        }
    }

    /**
     * Test that main layout references all required CSS files
     */
    public function test_main_layout_references_required_css(): void
    {
        $layoutPath = resource_path('views/layouts/main.blade.php');
        $this->assertFileExists($layoutPath, 'Main layout file does not exist');

        $content = File::get($layoutPath);
        
        $requiredCssFiles = [
            'resources/css/app.css',
            'resources/css/browser-compatibility.css',
            'resources/css/dark-mode.css',
            'resources/css/show-dark-mode.css'
        ];

        foreach ($requiredCssFiles as $cssFile) {
            $this->assertStringContainsString(
                $cssFile,
                $content,
                "Main layout does not reference CSS file: {$cssFile}"
            );
        }
    }

    /**
     * Test that Vite configuration file exists and is readable
     */
    public function test_vite_config_exists_and_readable(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        $this->assertFileExists($viteConfigPath, 'Vite configuration file does not exist');

        $content = File::get($viteConfigPath);
        $this->assertNotEmpty($content, 'Vite configuration file is empty');

        // Check that it contains Laravel plugin configuration
        $this->assertStringContainsString('laravel', $content);
        $this->assertStringContainsString('input:', $content);
    }

    /**
     * Test that asset validation plugin exists
     */
    public function test_asset_validation_plugin_exists(): void
    {
        $pluginPath = base_path('vite-plugins/asset-validation.js');
        $this->assertFileExists($pluginPath, 'Asset validation plugin does not exist');

        $content = File::get($pluginPath);
        $this->assertStringContainsString('assetValidationPlugin', $content);
        $this->assertStringContainsString('validateAssets', $content);
    }

    /**
     * Test CSS file structure and basic validation
     */
    public function test_css_files_basic_structure(): void
    {
        $cssFiles = [
            'resources/css/app.css',
            'resources/css/browser-compatibility.css', 
            'resources/css/dark-mode.css',
            'resources/css/show-dark-mode.css'
        ];

        foreach ($cssFiles as $cssFile) {
            $fullPath = base_path($cssFile);
            
            if (File::exists($fullPath)) {
                $content = File::get($fullPath);
                
                // Should not contain obvious syntax errors
                $this->assertStringNotContainsString('undefined', $content);
                $this->assertStringNotContainsString('null', $content);
                
                // Should contain CSS-like content
                $hasCssContent = (
                    str_contains($content, '{') && str_contains($content, '}')
                ) || str_contains($content, '@');
                
                $this->assertTrue(
                    $hasCssContent,
                    "CSS file does not contain valid CSS content: {$cssFile}"
                );
            }
        }
    }
}