import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { assetValidationPlugin } from './vite-plugins/asset-validation.js';

export default defineConfig({
    // CSS processing configuration
    css: {
        postcss: {
            plugins: [
                // PostCSS plugins will be loaded from postcss.config.js
            ]
        },
        // CSS modules configuration (if needed)
        modules: {
            localsConvention: 'camelCase'
        },
        // CSS preprocessing options
        preprocessorOptions: {
            scss: {
                additionalData: `@import "resources/css/variables.scss";`
            }
        }
    },
    
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/browser-compatibility.css',
                'resources/css/dark-mode.css',
                'resources/css/show-dark-mode.css',
                'resources/js/app.jsx'
            ],
            refresh: true,
        }),
        react(),
        assetValidationPlugin({
            strict: process.env.NODE_ENV === 'production',
            logLevel: 'warn'
        }),
    ],
    build: {
        cssCodeSplit: true,
        cssMinify: true,
        rollupOptions: {
            output: {
                // Optimized asset naming strategy for better caching
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    
                    // Images with organized structure
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        return `images/[name]-[hash:8][extname]`;
                    }
                    
                    // CSS files with categorized naming
                    if (/css/i.test(extType)) {
                        // Categorize CSS files for better organization
                        if (assetInfo.name.includes('app')) {
                            return `css/core/app-[hash:8][extname]`;
                        }
                        if (assetInfo.name.includes('show-dark-mode')) {
                            return `css/pages/show-dark-mode-[hash:8][extname]`;
                        }
                        if (assetInfo.name.includes('dark-mode')) {
                            return `css/themes/dark-mode-[hash:8][extname]`;
                        }
                        if (assetInfo.name.includes('browser-compatibility')) {
                            return `css/compatibility/browser-compat-[hash:8][extname]`;
                        }
                        return `css/[name]-[hash:8][extname]`;
                    }
                    
                    // JavaScript files
                    if (/js/i.test(extType)) {
                        return `js/[name]-[hash:8][extname]`;
                    }
                    
                    // Other assets
                    return `assets/[name]-[hash:8][extname]`;
                },
                
                // Optimize chunk naming for JavaScript
                chunkFileNames: (chunkInfo) => {
                    return `js/chunks/[name]-[hash:8].js`;
                },
                
                // Entry file naming
                entryFileNames: (chunkInfo) => {
                    return `js/[name]-[hash:8].js`;
                },
                
                // Manual chunks for better code splitting
                manualChunks: (id) => {
                    // Vendor libraries
                    if (id.includes('node_modules')) {
                        if (id.includes('react')) {
                            return 'vendor-react';
                        }
                        return 'vendor';
                    }
                    
                    // Page-specific chunks
                    if (id.includes('show-dark-mode')) {
                        return 'show-page';
                    }
                }
            }
        },
        
        // CSS optimization settings
        cssTarget: 'es2015',
        
        // Asset optimization
        assetsInlineLimit: 4096, // 4kb - inline smaller assets
        
        // Chunk size warnings
        chunkSizeWarningLimit: 1000
    },
    
    // Performance optimizations
    optimizeDeps: {
        include: ['react', 'react-dom'],
        exclude: ['@vite/client', '@vite/env']
    },
    
    // Server configuration for development
    server: {
        hmr: {
            overlay: false
        }
    }
});