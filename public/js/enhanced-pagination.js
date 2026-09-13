/**
 * Enhanced Pagination JavaScript
 * Simplified version to avoid conflicts
 */

class EnhancedPagination {
    constructor() {
        // Only initialize if not already initialized
        if (window.paginationInitialized) {
            return;
        }
        window.paginationInitialized = true;
        this.init();
    }

    init() {
        this.setupAccessibility();
        this.setupKeyboardShortcuts();
        this.preloadAdjacentPages();
    }

    // Removed duplicate functions to avoid conflicts with inline scripts

    setupKeyboardShortcuts() {
        // Simplified keyboard shortcuts without conflicts
        document.addEventListener('keydown', (e) => {
            if (document.activeElement.tagName !== 'INPUT' && 
                document.activeElement.tagName !== 'TEXTAREA' &&
                !document.activeElement.isContentEditable) {
                
                if (e.key === 'g' && e.ctrlKey) {
                    // Ctrl + G = Go to page (focus jump input)
                    e.preventDefault();
                    const jumpInput = document.getElementById('page-jump-input');
                    if (jumpInput) {
                        jumpInput.focus();
                        jumpInput.select();
                    }
                }
            }
        });
    }

    setupAccessibility() {
        // Add ARIA labels and improve screen reader support
        const paginationNav = document.querySelector('.enhanced-pagination');
        if (paginationNav) {
            paginationNav.setAttribute('role', 'navigation');
            paginationNav.setAttribute('aria-label', 'Navegación de páginas de noticias');
        }

        // Add live region for page changes
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.id = 'pagination-live-region';
        document.body.appendChild(liveRegion);

        // Announce page changes
        this.announceCurrentPage();
    }

    // Removed navigation functions to avoid conflicts

    announceCurrentPage() {
        const liveRegion = document.getElementById('pagination-live-region');
        const currentPageElement = document.querySelector('.page-item.active .page-link');
        
        if (liveRegion && currentPageElement) {
            const currentPage = currentPageElement.textContent.trim();
            const totalPages = this.getTotalPages();
            const totalItems = this.getTotalItems();
            
            liveRegion.textContent = `Página ${currentPage} de ${totalPages}. Mostrando ${totalItems} noticias en total.`;
        }
    }

    getTotalPages() {
        const lastPageLink = document.querySelector('.enhanced-pagination .page-link:last-child');
        if (lastPageLink && lastPageLink.textContent.match(/^\d+$/)) {
            return lastPageLink.textContent.trim();
        }
        
        // Fallback: count page number links
        const pageLinks = document.querySelectorAll('.enhanced-pagination .page-link');
        let maxPage = 1;
        pageLinks.forEach(link => {
            const pageNum = parseInt(link.textContent.trim());
            if (!isNaN(pageNum) && pageNum > maxPage) {
                maxPage = pageNum;
            }
        });
        return maxPage;
    }

    getTotalItems() {
        const paginationText = document.querySelector('.pagination-text');
        if (paginationText) {
            const match = paginationText.textContent.match(/de (\d+) noticias/);
            return match ? match[1] : '0';
        }
        return '0';
    }

    preloadAdjacentPages() {
        // Preload next and previous pages for faster navigation
        const nextLink = document.querySelector('.page-item:not(.disabled) .page-link[aria-label="Página siguiente"]');
        const prevLink = document.querySelector('.page-item:not(.disabled) .page-link[aria-label="Página anterior"]');
        
        if (nextLink) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = nextLink.href;
            document.head.appendChild(link);
        }
        
        if (prevLink) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = prevLink.href;
            document.head.appendChild(link);
        }
    }
}

// Initialize enhanced pagination when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new EnhancedPagination();
});

// Export for potential external use
window.EnhancedPagination = EnhancedPagination;