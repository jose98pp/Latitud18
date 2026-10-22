<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class AssetIntegrityTest extends TestCase
{
    /**
     * Test that all CSS files referenced in Blade templates exist in the filesystem
     */
    public function test_referenced_css_files_exist_in_filesystem(): void
    {
        $referencedCssFiles = $this->findReferencedCssFiles();

        foreach ($referencedCssFiles as $cssFile) {
            $fullPath = base_path($cssFile);
            $this->assertFileExists(
                $fullPath,
                "Referenced CSS file does not exist: {$cssFile}"
            );
        }
    }

    /**
     * Test that all CSS files are included in Vite configuration
     */
    public function test_css_files_are_configured_in_vite(): void
    {
        $referencedCssFiles = $this->findReferencedCssFiles();
        $viteConfig = $this->getViteConfiguration();

        foreach ($referencedCssFiles as $cssFile) {
            $this->assertContains(
                $cssFile,
                $viteConfig['input'],
                "CSS file referenced in Blade template but not configured in vite.config.js: {$cssFile}"
            );
        }
    }

    /**
     * Test that Vite build generates manifest with all required CSS assets
     */
    public function test_vite_build_includes_all_css_assets(): void
    {
        // Run Vite build
        // npm build runs separately; PHP tests only inspect its artifact and never delete it.

        // Check that manifest exists
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath, 'Vite manifest file does not exist');

        // Read manifest
        $manifest = json_decode(File::get($manifestPath), true);
        $this->assertIsArray($manifest, 'Vite manifest is not valid JSON');

        // Get referenced CSS files
        $referencedCssFiles = $this->findReferencedCssFiles();

        // Check that each CSS file appears in the manifest
        foreach ($referencedCssFiles as $cssFile) {
            $this->assertArrayHasKey(
                $cssFile,
                $manifest,
                "CSS file not found in Vite manifest: {$cssFile}"
            );

            // Verify the CSS file has proper structure in manifest
            $manifestEntry = $manifest[$cssFile];
            $this->assertArrayHasKey('file', $manifestEntry, "Manifest entry for {$cssFile} missing 'file' key");

            // Verify the actual built CSS file exists
            $builtCssPath = public_path('build/' . $manifestEntry['file']);
            $this->assertFileExists(
                $builtCssPath,
                "Built CSS file does not exist: {$manifestEntry['file']}"
            );
        }
    }

    /**
     * Test that no CSS files are configured but not referenced
     */
    public function test_no_orphaned_css_configuration(): void
    {
        $referencedCssFiles = $this->findReferencedCssFiles();
        $viteConfig = $this->getViteConfiguration();

        $configuredCssFiles = array_filter($viteConfig['input'], function($file) {
            return str_ends_with($file, '.css');
        });

        foreach ($configuredCssFiles as $cssFile) {
            $this->assertContains(
                $cssFile,
                $referencedCssFiles,
                "CSS file configured in vite.config.js but not referenced in any Blade template: {$cssFile}"
            );
        }
    }

    /**
     * Test that CSS files have valid syntax and can be processed
     */
    public function test_css_files_have_valid_syntax(): void
    {
        $referencedCssFiles = $this->findReferencedCssFiles();

        foreach ($referencedCssFiles as $cssFile) {
            $fullPath = base_path($cssFile);

            if (File::exists($fullPath)) {
                $content = File::get($fullPath);

                // Basic CSS syntax validation
                $this->assertValidCssSyntax($content, $cssFile);
            }
        }
    }

    /**
     * Find all CSS files referenced in Blade templates
     */
    private function findReferencedCssFiles(): array
    {
        $bladeFiles = File::glob(resource_path('views/**/*.blade.php'));
        $cssFiles = array_merge(\App\Helpers\AssetHelper::getBaseCssFiles(), ['resources/css/show-dark-mode.css', 'resources/css/portada.css']);

        foreach ($bladeFiles as $file) {
            $content = File::get($file);

            // Match @vite([...]) directives
            if (preg_match_all('/@vite\s*\(\s*\[(.*?)\]\s*\)/s', $content, $matches)) {
                foreach ($matches[1] as $arrayContent) {
                    // Find CSS file references
                    if (preg_match_all("/'([^']*\.css)'/", $arrayContent, $cssMatches)) {
                        foreach ($cssMatches[1] as $cssFile) {
                            $cssFiles[] = $cssFile;
                        }
                    }
                }
            }
        }

        return array_unique($cssFiles);
    }

    /**
     * Get Vite configuration from vite.config.js
     */
    private function getViteConfiguration(): array
    {
        preg_match_all("/'(resources\/[^']+\.(?:css|js|jsx))'/", File::get(base_path('vite.config.js')), $matches);
        return ['input'=>array_unique($matches[1])];
    }

    /**
     * Validate CSS syntax
     */
    private function assertValidCssSyntax(string $content, string $filename): void
    {
        // Basic CSS validation - check for balanced braces
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');

        $this->assertEquals(
            $openBraces,
            $closeBraces,
            "CSS file has unbalanced braces: {$filename}"
        );

        // Check for basic CSS structure
        $this->assertMatchesRegularExpression(
            '/[a-zA-Z0-9\-_\.\#\:\[\]]+\s*\{[^}]*\}/',
            $content,
            "CSS file does not contain valid CSS rules: {$filename}"
        );
    }
}
