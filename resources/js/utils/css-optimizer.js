/**
 * CSS Optimization Utilities
 * Provides runtime CSS optimization and loading strategies
 */

class CSSOptimizer {
    constructor() {
        this.loadedStyles = new Set();
        this.criticalStyles = new Set(['app', 'browser-compatibility', 'dark-mode']);
        this.pageSpecificStyles = new Map([
            ['show', ['show-dark-mode']]
        ]);
    }

    /**
     * Load CSS file asynchronously
     * @param {string} href - CSS file URL
     * @param {string} id - Unique identifier for the CSS file
     * @returns {Promise<void>}
     */
    async loadCSS(href, id) {
        if (this.loadedStyles.has(id)) {
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.id = `css-${id}`;
            
            link.onload = () => {
                this.loadedStyles.add(id);
                resolve();
            };
            
            link.onerror = () => {
                reject(new Error(`Failed to load CSS: ${href}`));
            };
            
            document.head.appendChild(link);
        });
    }

    /**
     * Preload CSS file for faster loading
     * @param {string} href - CSS file URL
     * @param {string} id - Unique identifier
     */
    preloadCSS(href, id) {
        if (this.loadedStyles.has(id)) {
            return;
        }

        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'style';
        link.href = href;
        link.id = `preload-${id}`;
        
        // Convert to stylesheet after load
        link.onload = () => {
            link.rel = 'stylesheet';
            this.loadedStyles.add(id);
        };
        
        document.head.appendChild(link);
    }

    /**
     * Remove unused CSS files
     * @param {string[]} keepIds - IDs of CSS files to keep
     */
    removeUnusedCSS(keepIds = []) {
        const allStylesheets = document.querySelectorAll('link[rel="stylesheet"]');
        
        allStylesheets.forEach(link => {
            const id = link.id.replace('css-', '');
            
            // Keep critical styles and specified styles
            if (this.criticalStyles.has(id) || keepIds.includes(id)) {
                return;
            }
            
            // Remove unused page-specific styles
            if (link.id.startsWith('css-') && !keepIds.includes(id)) {
                link.remove();
                this.loadedStyles.delete(id);
            }
        });
    }

    /**
     * Get page-specific CSS files for current route
     * @param {string} routeName - Current route name
     * @returns {string[]}
     */
    getPageSpecificCSS(routeName) {
        return this.pageSpecificStyles.get(routeName) || [];
    }

    /**
     * Optimize CSS loading for current page
     * @param {string} routeName - Current route name
     */
    optimizeForPage(routeName) {
        const pageCSS = this.getPageSpecificCSS(routeName);
        
        // Remove unused CSS from other pages
        const allPageCSS = Array.from(this.pageSpecificStyles.values()).flat();
        const unusedCSS = allPageCSS.filter(css => !pageCSS.includes(css));
        
        this.removeUnusedCSS([...this.criticalStyles, ...pageCSS]);
        
        // Preload page-specific CSS if not already loaded
        pageCSS.forEach(cssId => {
            if (!this.loadedStyles.has(cssId)) {
                // This would need to be integrated with Vite's asset manifest
                console.log(`Would preload CSS: ${cssId}`);
            }
        });
    }

    /**
     * Monitor CSS loading performance
     */
    monitorPerformance() {
        if (typeof performance !== 'undefined' && performance.getEntriesByType) {
            const cssResources = performance.getEntriesByType('resource')
                .filter(entry => entry.name.includes('.css'));
            
            cssResources.forEach(resource => {
                console.log(`CSS Load Time: ${resource.name} - ${resource.duration}ms`);
            });
        }
    }

    /**
     * Initialize CSS optimization for the current page
     */
    init() {
        // Get current route from Laravel (would need to be passed from blade template)
        const routeName = window.currentRoute || 'default';
        
        // Optimize CSS loading
        this.optimizeForPage(routeName);
        
        // Monitor performance in development
        if (process.env.NODE_ENV === 'development') {
            setTimeout(() => this.monitorPerformance(), 2000);
        }
    }
}

// Export for use in other modules
export default CSSOptimizer;

// Auto-initialize if in browser environment
if (typeof window !== 'undefined') {
    const optimizer = new CSSOptimizer();
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => optimizer.init());
    } else {
        optimizer.init();
    }
    
    // Make available globally for debugging
    window.CSSOptimizer = optimizer;
}