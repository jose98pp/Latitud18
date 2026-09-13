/**
 * CSS Optimization and Browser Compatibility Handler
 * Handles CSS loading, browser detection, and performance optimization
 */

(function() {
    'use strict';

    // Browser detection and feature support
    const BrowserSupport = {
        // Check for CSS Grid support
        supportsGrid: function() {
            return CSS && CSS.supports && CSS.supports('display', 'grid');
        },

        // Check for CSS Variables support
        supportsVariables: function() {
            return CSS && CSS.supports && CSS.supports('--css', 'variables');
        },

        // Check for object-fit support
        supportsObjectFit: function() {
            return CSS && CSS.supports && CSS.supports('object-fit', 'cover');
        },

        // Check for aspect-ratio support
        supportsAspectRatio: function() {
            return CSS && CSS.supports && CSS.supports('aspect-ratio', '16/9');
        },

        // Check for flexbox support
        supportsFlexbox: function() {
            return CSS && CSS.supports && CSS.supports('display', 'flex');
        },

        // Check for transform support
        supportsTransform: function() {
            return CSS && CSS.supports && CSS.supports('transform', 'translateX(0)');
        },

        // Check for transition support
        supportsTransition: function() {
            return CSS && CSS.supports && CSS.supports('transition', 'all 0.3s ease');
        }
    };

    // CSS Loading Optimization
    const CSSLoader = {
        // Load CSS asynchronously
        loadAsync: function(href, media) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.media = media || 'all';
            
            // Add to head
            document.head.appendChild(link);
            
            return link;
        },

        // Preload CSS
        preload: function(href) {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'style';
            link.href = href;
            link.onload = function() {
                this.onload = null;
                this.rel = 'stylesheet';
            };
            
            document.head.appendChild(link);
            
            // Fallback for browsers that don't support preload
            const noscript = document.createElement('noscript');
            const fallbackLink = document.createElement('link');
            fallbackLink.rel = 'stylesheet';
            fallbackLink.href = href;
            noscript.appendChild(fallbackLink);
            document.head.appendChild(noscript);
            
            return link;
        }
    };

    // Performance Optimization
    const PerformanceOptimizer = {
        // Add will-change property to elements that will be animated
        optimizeAnimations: function() {
            const animatedElements = document.querySelectorAll(
                '.card-hover, .carousel-btn, .social-icons a, .ratio img, .fade-in'
            );
            
            animatedElements.forEach(function(element) {
                element.style.willChange = 'transform';
            });
        },

        // Enable GPU acceleration for animations
        enableGPUAcceleration: function() {
            const elements = document.querySelectorAll(
                '.card-hover, .carousel-btn, .social-icons a, .ratio img'
            );
            
            elements.forEach(function(element) {
                element.style.transform = element.style.transform || 'translateZ(0)';
                element.style.backfaceVisibility = 'hidden';
            });
        },

        // Optimize images for better performance
        optimizeImages: function() {
            const images = document.querySelectorAll('img');
            
            images.forEach(function(img) {
                // Add loading="lazy" for images below the fold
                if (!img.hasAttribute('loading')) {
                    const rect = img.getBoundingClientRect();
                    if (rect.top > window.innerHeight) {
                        img.setAttribute('loading', 'lazy');
                    }
                }
                
                // Add decoding="async" for better performance
                if (!img.hasAttribute('decoding')) {
                    img.setAttribute('decoding', 'async');
                }
            });
        }
    };

    // Browser Compatibility Fixes
    const CompatibilityFixes = {
        // Add fallback classes for unsupported features
        addFallbackClasses: function() {
            const html = document.documentElement;
            
            // Add classes based on feature support
            if (!BrowserSupport.supportsGrid()) {
                html.classList.add('no-grid');
            }
            
            if (!BrowserSupport.supportsVariables()) {
                html.classList.add('no-variables');
            }
            
            if (!BrowserSupport.supportsObjectFit()) {
                html.classList.add('no-object-fit');
            }
            
            if (!BrowserSupport.supportsAspectRatio()) {
                html.classList.add('no-aspect-ratio');
            }
            
            if (!BrowserSupport.supportsFlexbox()) {
                html.classList.add('no-flexbox');
            }
        },

        // Fix object-fit for older browsers
        fixObjectFit: function() {
            if (!BrowserSupport.supportsObjectFit()) {
                const images = document.querySelectorAll('.image-fit');
                
                images.forEach(function(img) {
                    const container = img.parentElement;
                    if (container && container.classList.contains('image-container')) {
                        img.style.position = 'absolute';
                        img.style.top = '50%';
                        img.style.left = '50%';
                        img.style.transform = 'translate(-50%, -50%)';
                        img.style.minWidth = '100%';
                        img.style.minHeight = '100%';
                    }
                });
            }
        },

        // Fix aspect-ratio for older browsers
        fixAspectRatio: function() {
            if (!BrowserSupport.supportsAspectRatio()) {
                const elements16x9 = document.querySelectorAll('.aspect-ratio-16-9');
                const elements4x3 = document.querySelectorAll('.aspect-ratio-4-3');
                
                elements16x9.forEach(function(element) {
                    element.style.position = 'relative';
                    element.style.width = '100%';
                    element.style.height = '0';
                    element.style.paddingBottom = '56.25%'; // 9/16
                });
                
                elements4x3.forEach(function(element) {
                    element.style.position = 'relative';
                    element.style.width = '100%';
                    element.style.height = '0';
                    element.style.paddingBottom = '75%'; // 3/4
                });
            }
        }
    };

    // Error Handling
    const ErrorHandler = {
        // Handle CSS loading errors
        handleCSSError: function(link) {
            link.onerror = function() {
                console.warn('Failed to load CSS:', this.href);
                // Try to load a fallback CSS if available
                if (this.href.includes('optimized.css')) {
                    console.log('Loading fallback CSS...');
                    CSSLoader.loadAsync('/css/app.css');
                }
            };
        },

        // Handle font loading errors
        handleFontError: function() {
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function() {
                    console.log('Fonts loaded successfully');
                }).catch(function(error) {
                    console.warn('Font loading error:', error);
                    // Apply fallback font
                    document.body.style.fontFamily = 'Arial, sans-serif';
                });
            }
        }
    };

    // Initialize optimization when DOM is ready
    function initialize() {
        // Add compatibility classes
        CompatibilityFixes.addFallbackClasses();
        
        // Apply compatibility fixes
        CompatibilityFixes.fixObjectFit();
        CompatibilityFixes.fixAspectRatio();
        
        // Optimize performance
        PerformanceOptimizer.optimizeAnimations();
        PerformanceOptimizer.enableGPUAcceleration();
        PerformanceOptimizer.optimizeImages();
        
        // Handle errors
        ErrorHandler.handleFontError();
        
        // Add error handlers to existing CSS links
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"]');
        cssLinks.forEach(ErrorHandler.handleCSSError);
        
        console.log('CSS optimization initialized');
    }

    // Run initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Re-run optimizations when new content is added
    const observer = new MutationObserver(function(mutations) {
        let shouldOptimize = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                shouldOptimize = true;
            }
        });
        
        if (shouldOptimize) {
            PerformanceOptimizer.optimizeImages();
            CompatibilityFixes.fixObjectFit();
            CompatibilityFixes.fixAspectRatio();
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Expose utilities globally for debugging
    window.CSSOptimization = {
        BrowserSupport: BrowserSupport,
        CSSLoader: CSSLoader,
        PerformanceOptimizer: PerformanceOptimizer,
        CompatibilityFixes: CompatibilityFixes
    };

})();