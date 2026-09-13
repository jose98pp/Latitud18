<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class AssetHelper
{
    /**
     * Get the CSS files that should be loaded for the current route
     *
     * @return array
     */
    public static function getConditionalCssFiles(): array
    {
        $currentRoute = Route::currentRouteName();
        $conditionalFiles = [];

        // Page-specific CSS files
        if ($currentRoute === 'show') {
            $conditionalFiles[] = 'resources/css/show-dark-mode.css';
        }

        return $conditionalFiles;
    }

    /**
     * Get the base CSS files that should always be loaded
     *
     * @return array
     */
    public static function getBaseCssFiles(): array
    {
        return [
            'resources/css/app.css',
            'resources/css/browser-compatibility.css',
            'resources/css/dark-mode.css'
        ];
    }

    /**
     * Get all CSS files for the current page
     *
     * @return array
     */
    public static function getAllCssFiles(): array
    {
        return array_merge(
            self::getBaseCssFiles(),
            self::getConditionalCssFiles()
        );
    }

    /**
     * Check if we're on a show page
     *
     * @return bool
     */
    public static function isShowPage(): bool
    {
        return Route::currentRouteName() === 'show';
    }

    /**
     * Get CSS loading strategy for the current page
     *
     * @return array
     */
    public static function getCssLoadingStrategy(): array
    {
        $currentRoute = Route::currentRouteName();
        
        return [
            'critical' => self::getBaseCssFiles(),
            'conditional' => self::getConditionalCssFiles(),
            'preload' => self::getPreloadCssFiles($currentRoute),
            'defer' => self::getDeferredCssFiles($currentRoute)
        ];
    }

    /**
     * Get CSS files that should be preloaded for better performance
     *
     * @param string $routeName
     * @return array
     */
    public static function getPreloadCssFiles(string $routeName): array
    {
        // Preload page-specific CSS for likely next pages
        $preloadFiles = [];
        
        if ($routeName === 'portada') {
            // From homepage, users likely go to show pages
            $preloadFiles[] = 'resources/css/show-dark-mode.css';
        }
        
        return $preloadFiles;
    }

    /**
     * Get CSS files that can be deferred (loaded after critical content)
     *
     * @param string $routeName
     * @return array
     */
    public static function getDeferredCssFiles(string $routeName): array
    {
        // Files that can be loaded after initial render
        return [];
    }

    /**
     * Get asset optimization metadata
     *
     * @return array
     */
    public static function getOptimizationMetadata(): array
    {
        return [
            'css_code_split' => true,
            'css_minify' => app()->environment('production'),
            'asset_versioning' => true,
            'preload_strategy' => 'conditional',
            'cache_strategy' => 'content_hash'
        ];
    }
}