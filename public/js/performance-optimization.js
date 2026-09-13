/**
 * Performance Optimization JavaScript
 * Handles lazy loading, image optimization, and responsive behavior
 */

class PerformanceOptimizer {
    constructor() {
        this.lazyImages = [];
        this.imageObserver = null;
        this.init();
    }

    init() {
        this.setupLazyLoading();
        this.optimizeImages();
        this.setupResponsiveOptimizations();
        this.setupPerformanceMonitoring();
        this.preloadCriticalResources();
    }

    setupLazyLoading() {
        // Check for Intersection Observer support
        if ('IntersectionObserver' in window) {
            this.imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        observer.unobserve(img);
                    }
                });
            }, {
                // Load images when they're 50px away from viewport
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            this.observeLazyImages();
        } else {
            // Fallback for browsers without Intersection Observer
            this.loadAllImages();
        }
    }

    observeLazyImages() {
        const lazyImages = document.querySelectorAll('.lazy-image[data-src]');
        lazyImages.forEach(img => {
            this.imageObserver.observe(img);
        });
    }

    loadImage(img) {
        const src = img.getAttribute('data-src');
        if (!src) return;

        // Create a new image to preload
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            // Image loaded successfully
            img.src = src;
            img.classList.add('loaded');
            img.removeAttribute('data-src');
            
            // Add fade-in animation
            img.style.opacity = '0';
            setTimeout(() => {
                img.style.opacity = '1';
            }, 10);
        };

        imageLoader.onerror = () => {
            // Image failed to load
            img.src = '/images/default-news.svg';
            img.classList.add('error');
            img.removeAttribute('data-src');
        };

        // Start loading
        imageLoader.src = src;
    }

    loadAllImages() {
        // Fallback: load all images immediately
        const lazyImages = document.querySelectorAll('.lazy-image[data-src]');
        lazyImages.forEach(img => {
            this.loadImage(img);
        });
    }

    optimizeImages() {
        // Add responsive image handling
        this.setupResponsiveImages();
        
        // Optimize image loading based on connection speed
        this.optimizeForConnection();
        
        // Setup image error handling
        this.setupImageErrorHandling();
    }

    setupResponsiveImages() {
        const images = document.querySelectorAll('.lazy-image');
        
        images.forEach(img => {
            // Add responsive classes based on screen size
            const updateImageSize = () => {
                const screenWidth = window.innerWidth;
                
                if (screenWidth < 576) {
                    img.classList.add('img-mobile');
                } else if (screenWidth < 768) {
                    img.classList.add('img-tablet');
                } else {
                    img.classList.add('img-desktop');
                }
            };

            updateImageSize();
            window.addEventListener('resize', this.debounce(updateImageSize, 250));
        });
    }

    optimizeForConnection() {
        // Check connection speed and adjust image quality
        if ('connection' in navigator) {
            const connection = navigator.connection;
            
            if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                // Low quality images for slow connections
                document.body.classList.add('low-bandwidth');
            } else if (connection.effectiveType === '3g') {
                // Medium quality images
                document.body.classList.add('medium-bandwidth');
            } else {
                // High quality images for fast connections
                document.body.classList.add('high-bandwidth');
            }
        }
    }

    setupImageErrorHandling() {
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'IMG') {
                const img = e.target;
                
                // Don't retry if already tried
                if (img.classList.contains('error-handled')) return;
                
                img.classList.add('error-handled');
                
                // Try to load a default image
                img.src = '/images/default-news.svg';
                
                // Add error styling
                img.closest('.news-card-image')?.classList.add('image-error');
            }
        }, true);
    }

    setupResponsiveOptimizations() {
        // Optimize layout for different screen sizes
        this.setupResponsiveGrid();
        this.setupMobileOptimizations();
        this.setupTabletOptimizations();
    }

    setupResponsiveGrid() {
        const updateGrid = () => {
            const grid = document.querySelector('.news-cards-grid');
            if (!grid) return;

            const screenWidth = window.innerWidth;
            const cardCount = grid.children.length;

            // Optimize grid columns based on screen size and card count
            if (screenWidth < 576) {
                grid.style.gridTemplateColumns = '1fr';
            } else if (screenWidth < 768) {
                grid.style.gridTemplateColumns = cardCount === 1 ? '1fr' : 'repeat(2, 1fr)';
            } else if (screenWidth < 1024) {
                grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(300px, 1fr))';
            } else {
                grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(350px, 1fr))';
            }
        };

        updateGrid();
        window.addEventListener('resize', this.debounce(updateGrid, 250));
    }

    setupMobileOptimizations() {
        if (window.innerWidth < 768) {
            // Reduce animations on mobile for better performance
            document.body.classList.add('mobile-optimized');
            
            // Simplify hover effects
            const cards = document.querySelectorAll('.news-card');
            cards.forEach(card => {
                card.addEventListener('touchstart', () => {
                    card.classList.add('touch-active');
                });
                
                card.addEventListener('touchend', () => {
                    setTimeout(() => {
                        card.classList.remove('touch-active');
                    }, 150);
                });
            });
        }
    }

    setupTabletOptimizations() {
        if (window.innerWidth >= 768 && window.innerWidth < 1024) {
            document.body.classList.add('tablet-optimized');
            
            // Optimize touch interactions for tablets
            this.setupTouchOptimizations();
        }
    }

    setupTouchOptimizations() {
        // Improve touch interactions
        const interactiveElements = document.querySelectorAll('.news-card, .page-link, .filter-btn');
        
        interactiveElements.forEach(element => {
            element.style.minHeight = '44px'; // Minimum touch target size
            element.style.minWidth = '44px';
        });
    }

    setupPerformanceMonitoring() {
        // Monitor performance metrics
        if ('PerformanceObserver' in window) {
            // Monitor Largest Contentful Paint
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                
                if (lastEntry.loadTime > 2500) {
                    console.warn('LCP is slow:', lastEntry.loadTime);
                    this.optimizeForSlowLoading();
                }
            });
            
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

            // Monitor Cumulative Layout Shift
            const clsObserver = new PerformanceObserver((list) => {
                let clsValue = 0;
                for (const entry of list.getEntries()) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                }
                
                if (clsValue > 0.1) {
                    console.warn('CLS is high:', clsValue);
                    this.reduceLayoutShift();
                }
            });
            
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        }
    }

    optimizeForSlowLoading() {
        // Reduce animations and effects for slow loading
        document.body.classList.add('slow-loading');
        
        // Disable non-essential animations
        const style = document.createElement('style');
        style.textContent = `
            .slow-loading * {
                animation-duration: 0.1s !important;
                transition-duration: 0.1s !important;
            }
        `;
        document.head.appendChild(style);
    }

    reduceLayoutShift() {
        // Add explicit dimensions to prevent layout shift
        const images = document.querySelectorAll('.lazy-image');
        images.forEach(img => {
            if (!img.style.aspectRatio) {
                img.style.aspectRatio = '4/3'; // Default aspect ratio
            }
        });
    }

    preloadCriticalResources() {
        // Preload critical CSS and fonts
        const criticalResources = [
            '/css/news-cards.css',
            '/css/news-views.css',
            '/css/enhanced-pagination.css'
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'style';
            link.href = resource;
            document.head.appendChild(link);
        });

        // Preload next page if pagination exists
        this.preloadNextPage();
    }

    preloadNextPage() {
        const nextPageLink = document.querySelector('.page-link[aria-label="Página siguiente"]');
        if (nextPageLink && !nextPageLink.closest('.page-item.disabled')) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = nextPageLink.href;
            document.head.appendChild(link);
        }
    }

    // Utility function for debouncing
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public method to refresh lazy loading (useful after AJAX updates)
    refreshLazyLoading() {
        if (this.imageObserver) {
            this.observeLazyImages();
        }
    }

    // Public method to optimize new content
    optimizeNewContent(container) {
        const newImages = container.querySelectorAll('.lazy-image[data-src]');
        newImages.forEach(img => {
            if (this.imageObserver) {
                this.imageObserver.observe(img);
            } else {
                this.loadImage(img);
            }
        });
    }
}

// Initialize performance optimizer when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.performanceOptimizer = new PerformanceOptimizer();
});

// Export for external use
window.PerformanceOptimizer = PerformanceOptimizer;