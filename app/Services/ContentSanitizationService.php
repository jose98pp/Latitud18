<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class ContentSanitizationService
{
    private HTMLPurifier $purifier;
    
    public function __construct()
    {
        $this->purifier = $this->createPurifier();
    }
    
    /**
     * Sanitize HTML content to prevent XSS attacks and clean unwanted characters
     */
    public function sanitizeContent(?string $content): string
    {
        if (empty($content)) {
            return '';
        }
        
        // First remove unwanted characters
        $content = $this->removeUnwantedCharacters($content);
        
        // Then sanitize HTML
        return $this->purifier->purify($content);
    }
    
    /**
     * Process rich text content from editor, handling HTML and cleaning unwanted characters
     */
    public function processRichTextContent(?string $content): string
    {
        if (empty($content)) {
            return '';
        }
        
        // Remove unwanted characters first
        $content = $this->removeUnwantedCharacters($content);
        
        // Decode HTML entities that might have been double-encoded
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up whitespace and normalize line breaks
        $content = $this->normalizeWhitespace($content);
        
        // Sanitize HTML while preserving rich text formatting
        $content = $this->purifier->purify($content);
        
        return $content;
    }
    
    /**
     * Remove unwanted characters and clean up text
     */
    public function removeUnwantedCharacters(?string $content): string
    {
        if (empty($content)) {
            return '';
        }
        
        // Remove zero-width characters and other invisible characters
        $content = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $content);
        
        // Remove or replace problematic characters that can cause display issues
        $unwantedChars = [
            // Remove BOM (Byte Order Mark)
            "\xEF\xBB\xBF" => '',
            // Replace smart quotes with regular quotes
            "\u{201C}" => '"', // Left double quotation mark
            "\u{201D}" => '"', // Right double quotation mark
            "\u{2018}" => "'", // Left single quotation mark
            "\u{2019}" => "'", // Right single quotation mark
            // Replace em and en dashes with regular hyphens
            "\u{2014}" => '-', // Em dash
            "\u{2013}" => '-', // En dash
            // Remove or replace other problematic characters
            "\u{2026}" => '...', // Horizontal ellipsis
            // Remove null bytes and control characters (except tabs, newlines, carriage returns)
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/' => '',
        ];
        
        foreach ($unwantedChars as $search => $replace) {
            if (strpos($search, '/') === 0) {
                // It's a regex pattern
                $content = preg_replace($search, $replace, $content);
            } else {
                // It's a literal string
                $content = str_replace($search, $replace, $content);
            }
        }
        
        // Fix encoding issues - ensure proper UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        return $content;
    }
    
    /**
     * Normalize whitespace and line breaks
     */
    private function normalizeWhitespace(string $content): string
    {
        // Normalize different types of line breaks to \n
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Remove excessive whitespace but preserve intentional formatting
        $content = preg_replace('/[ \t]+/', ' ', $content);
        
        // Remove excessive line breaks (more than 2 consecutive)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Trim whitespace from beginning and end
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Validate content length and return error message if invalid
     */
    public function validateContentLength(?string $content, int $maxLength = 1000000): ?string
    {
        if (empty($content)) {
            return "El contenido no puede estar vacío.";
        }
        
        $cleanContent = strip_tags($content);
        
        if (strlen($cleanContent) > $maxLength) {
            return "El contenido excede el límite máximo de {$maxLength} caracteres.";
        }
        
        if (strlen($cleanContent) < 10) {
            return "El contenido debe tener al menos 10 caracteres.";
        }
        
        return null;
    }
    
    /**
     * Create and configure HTML Purifier instance
     */
    private function createPurifier(): HTMLPurifier
    {
        $config = HTMLPurifier_Config::createDefault();
        
        // Set cache directory
        $cacheDir = storage_path('app/htmlpurifier');
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cacheDir);
        
        // Allow specific HTML elements and attributes
        $config->set('HTML.Allowed', implode(',', [
            // Text formatting
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
            
            // Headings
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            
            // Lists
            'ul', 'ol', 'li',
            
            // Links
            'a[href|title|target]',
            
            // Images
            'img[src|alt|title|width|height|class]',
            
            // Tables
            'table[class]', 'thead', 'tbody', 'tr', 'th[colspan|rowspan]', 'td[colspan|rowspan]',
            
            // Divs and spans for styling
            'div[class|style]', 'span[class|style]',
            
            // Block quotes
            'blockquote', 'cite'
        ]));
        
        // Allow specific CSS properties for styling
        $config->set('CSS.AllowedProperties', [
            'color', 'background-color', 'font-size', 'font-weight', 'font-style',
            'text-align', 'text-decoration', 'margin', 'padding',
            'border', 'border-color', 'border-style', 'border-width'
        ]);
        
        // Configure links
        $config->set('HTML.TargetBlank', true); // Add target="_blank" to external links
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        
        // Image configuration
        $config->set('HTML.MaxImgLength', 1200);
        $config->set('CSS.MaxImgLength', '1200px');
        
        // Security settings
        $config->set('HTML.Nofollow', true); // Add rel="nofollow" to external links
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        
        return new HTMLPurifier($config);
    }
    
    /**
     * Get allowed HTML tags as a string for validation messages
     */
    public function getAllowedTagsString(): string
    {
        return 'p, br, strong, b, em, i, u, s, sub, sup, h1-h6, ul, ol, li, a, img, table, thead, tbody, tr, th, td, div, span, blockquote, cite';
    }
    
    /**
     * Get clean text excerpt for previews and summaries
     */
    public function getCleanExcerpt(?string $content, int $length = 200): string
    {
        if (empty($content)) {
            return '';
        }
        
        // Remove unwanted characters first
        $content = $this->removeUnwantedCharacters($content);
        
        // Strip all HTML tags
        $content = strip_tags($content);
        
        // Normalize whitespace
        $content = $this->normalizeWhitespace($content);
        
        // Truncate to desired length
        if (mb_strlen($content) > $length) {
            $content = mb_substr($content, 0, $length);
            // Try to break at word boundary
            $lastSpace = mb_strrpos($content, ' ');
            if ($lastSpace !== false && $lastSpace > $length * 0.8) {
                $content = mb_substr($content, 0, $lastSpace);
            }
            $content .= '...';
        }
        
        return $content;
    }
    
    /**
     * Check if content contains potentially dangerous elements
     */
    public function hasDangerousContent(?string $content): array
    {
        $warnings = [];
        
        if (empty($content)) {
            return $warnings;
        }
        
        // Check for script tags
        if (preg_match('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', $content)) {
            $warnings[] = 'Contenido contiene etiquetas script que serán removidas.';
        }
        
        // Check for event handlers
        if (preg_match('/on\w+\s*=/i', $content)) {
            $warnings[] = 'Contenido contiene manejadores de eventos JavaScript que serán removidos.';
        }
        
        // Check for javascript: URLs
        if (preg_match('/javascript\s*:/i', $content)) {
            $warnings[] = 'Contenido contiene URLs JavaScript que serán removidas.';
        }
        
        // Check for style with javascript
        if (preg_match('/style\s*=\s*["\'][^"\']*expression\s*\(/i', $content)) {
            $warnings[] = 'Contenido contiene CSS con expresiones JavaScript que serán removidas.';
        }
        
        return $warnings;
    }
}