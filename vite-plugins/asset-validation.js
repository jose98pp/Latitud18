import fs from 'fs';
import path from 'path';
import { glob } from 'glob';

/**
 * Vite plugin for validating CSS asset references
 * Ensures all CSS files referenced in @vite directives exist in vite.config.js and filesystem
 */
export function assetValidationPlugin(options = {}) {
    const {
        bladePattern = 'resources/views/**/*.blade.php',
        cssPattern = 'resources/css/**/*.css',
        strict = true,
        logLevel = 'warn'
    } = options;

    let config;
    let inputFiles = [];

    // Define validation function outside of plugin object
    function validateAssets() {
            try {
                const errors = [];
                const warnings = [];

                // 1. Find all CSS files referenced in Blade templates
                const referencedCssFiles = findReferencedCssFiles(bladePattern);
                
                // 2. Find all CSS files in the filesystem
                const existingCssFiles = findExistingCssFiles(cssPattern);
                
                // 3. Get configured input files (CSS only)
                const configuredCssFiles = inputFiles.filter(file => file.endsWith('.css'));

                // 4. Validate that referenced CSS files exist in filesystem
                referencedCssFiles.forEach(cssFile => {
                    const fullPath = path.resolve(cssFile);
                    if (!fs.existsSync(fullPath)) {
                        errors.push(`Referenced CSS file does not exist: ${cssFile}`);
                    }
                });

                // 5. Validate that referenced CSS files are configured in Vite
                referencedCssFiles.forEach(cssFile => {
                    if (!configuredCssFiles.includes(cssFile)) {
                        errors.push(`CSS file referenced in Blade template but not configured in vite.config.js: ${cssFile}`);
                    }
                });

                // 6. Warn about configured CSS files that aren't referenced
                configuredCssFiles.forEach(cssFile => {
                    if (!referencedCssFiles.includes(cssFile)) {
                        warnings.push(`CSS file configured in vite.config.js but not referenced in any Blade template: ${cssFile}`);
                    }
                });

                // 7. Report findings
                if (errors.length > 0) {
                    const errorMessage = `Asset Validation Errors:\n${errors.map(e => `  - ${e}`).join('\n')}`;
                    if (strict) {
                        throw new Error(errorMessage);
                    } else {
                        console.error('\x1b[31m%s\x1b[0m', errorMessage);
                    }
                }

                if (warnings.length > 0 && logLevel !== 'silent') {
                    const warningMessage = `Asset Validation Warnings:\n${warnings.map(w => `  - ${w}`).join('\n')}`;
                    console.warn('\x1b[33m%s\x1b[0m', warningMessage);
                }

                if (errors.length === 0 && warnings.length === 0) {
                    console.log('\x1b[32m%s\x1b[0m', '✓ Asset validation passed - all CSS references are properly configured');
                }

                return {
                    errors,
                    warnings,
                    referencedFiles: referencedCssFiles,
                    configuredFiles: configuredCssFiles,
                    existingFiles: existingCssFiles
                };

            } catch (error) {
                console.error('Asset validation failed:', error.message);
                if (strict) {
                    throw error;
                }
            }
        }

    // Helper functions
    function findReferencedCssFiles(pattern) {
            const bladeFiles = glob.sync(pattern);
            const cssFiles = new Set();

            bladeFiles.forEach(file => {
                try {
                    const content = fs.readFileSync(file, 'utf8');
                    
                    // Match @vite([...]) directives
                    const viteMatches = content.match(/@vite\s*\(\s*\[(.*?)\]\s*\)/gs);
                    
                    if (viteMatches) {
                        viteMatches.forEach(match => {
                            // Extract the array content
                            const arrayContent = match.match(/\[(.*?)\]/s)[1];
                            
                            // Find CSS file references
                            const cssMatches = arrayContent.match(/'([^']*\.css)'/g);
                            if (cssMatches) {
                                cssMatches.forEach(cssMatch => {
                                    const cssFile = cssMatch.replace(/'/g, '');
                                    cssFiles.add(cssFile);
                                });
                            }
                        });
                    }
                } catch (error) {
                    console.warn(`Warning: Could not read file ${file}:`, error.message);
                }
            });

            return Array.from(cssFiles);
        }

    function findExistingCssFiles(pattern) {
        return glob.sync(pattern);
    }

    return {
        name: 'asset-validation',
        configResolved(resolvedConfig) {
            config = resolvedConfig;
            // Extract input files from Laravel Vite plugin configuration
            const laravelPlugin = config.plugins.find(plugin => plugin.name === 'laravel');
            if (laravelPlugin) {
                // Try different ways to access the input configuration
                if (laravelPlugin.__vitePlugin && laravelPlugin.__vitePlugin.input) {
                    inputFiles = laravelPlugin.__vitePlugin.input;
                } else if (laravelPlugin.config && laravelPlugin.config.input) {
                    inputFiles = laravelPlugin.config.input;
                } else {
                    // Fallback: extract from the original config
                    inputFiles = [
                        'resources/css/app.css', 
                        'resources/css/browser-compatibility.css',
                        'resources/css/dark-mode.css',
                        'resources/css/show-dark-mode.css',
                        'resources/js/app.jsx'
                    ];
                }
            }
        },
        buildStart() {
            validateAssets();
        },
        configureServer(server) {
            // Run validation in development mode
            validateAssets();
            
            // Watch for changes in blade files and re-validate
            server.watcher.add(bladePattern);
            server.watcher.on('change', (file) => {
                if (file.endsWith('.blade.php')) {
                    validateAssets();
                }
            });
        }
    };
}