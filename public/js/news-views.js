/**
 * News Views Management System
 * Handles grid/list view toggle with localStorage persistence
 */

class NewsViewManager {
    constructor() {
        this.container = document.querySelector('.news-container');
        this.gridBtn = document.getElementById('grid-view-btn');
        this.listBtn = document.getElementById('list-view-btn');
        this.currentView = this.getStoredView() || 'grid';
        
        this.init();
    }
    
    init() {
        if (!this.container) {
            console.warn('News container not found');
            return;
        }
        
        // Set initial view
        this.setView(this.currentView, false);
        
        // Bind event listeners
        this.bindEvents();
        
        // Update button states
        this.updateButtonStates();
        
        console.log('News View Manager initialized with view:', this.currentView);
    }
    
    bindEvents() {
        if (this.gridBtn) {
            this.gridBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchToGrid();
            });
        }
        
        if (this.listBtn) {
            this.listBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchToList();
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + G for grid view
            if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
                e.preventDefault();
                this.switchToGrid();
            }
            
            // Ctrl/Cmd + L for list view
            if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
                e.preventDefault();
                this.switchToList();
            }
        });
        
        // Handle window resize for responsive behavior
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
    }
    
    switchToGrid() {
        if (this.currentView !== 'grid') {
            this.setView('grid');
            this.saveView('grid');
            this.trackViewChange('grid');
        }
    }
    
    switchToList() {
        if (this.currentView !== 'list') {
            this.setView('list');
            this.saveView('list');
            this.trackViewChange('list');
        }
    }
    
    setView(view, animate = true) {
        if (!this.container) return;
        
        // Add loading state if animating
        if (animate) {
            this.container.classList.add('view-changing');
        }
        
        // Remove existing view classes
        this.container.classList.remove('grid-view', 'list-view');
        
        // Add new view class
        this.container.classList.add(`${view}-view`);
        
        // Update current view
        this.currentView = view;
        
        // Update button states
        this.updateButtonStates();
        
        // Handle animation
        if (animate) {
            setTimeout(() => {
                this.container.classList.remove('view-changing');
                this.container.classList.add('view-changed');
                
                setTimeout(() => {
                    this.container.classList.remove('view-changed');
                }, 300);
            }, 50);
        }
        
        // Trigger custom event
        this.dispatchViewChangeEvent(view);
        
        // Re-initialize any card interactions
        this.reinitializeCardInteractions();
    }
    
    updateButtonStates() {
        if (this.gridBtn && this.listBtn) {
            // Remove active class from both
            this.gridBtn.classList.remove('active');
            this.listBtn.classList.remove('active');
            
            // Add active class to current view button
            if (this.currentView === 'grid') {
                this.gridBtn.classList.add('active');
            } else {
                this.listBtn.classList.add('active');
            }
            
            // Update ARIA attributes
            this.gridBtn.setAttribute('aria-pressed', this.currentView === 'grid');
            this.listBtn.setAttribute('aria-pressed', this.currentView === 'list');
        }
    }
    
    getStoredView() {
        try {
            return localStorage.getItem('news-view-preference');
        } catch (e) {
            console.warn('localStorage not available:', e);
            return null;
        }
    }
    
    saveView(view) {
        try {
            localStorage.setItem('news-view-preference', view);
        } catch (e) {
            console.warn('Could not save view preference:', e);
        }
    }
    
    handleResize() {
        const width = window.innerWidth;
        
        // Auto-switch to list view on very small screens if in grid view
        if (width < 640 && this.currentView === 'grid') {
            // Don't auto-switch, but could add this behavior if needed
            // this.switchToList();
        }
        
        // Reinitialize interactions after resize
        this.reinitializeCardInteractions();
    }
    
    reinitializeCardInteractions() {
        // Re-setup any card-specific interactions that might need refreshing
        const cards = document.querySelectorAll('.news-card');
        
        cards.forEach((card, index) => {
            // Reset animation delays for entrance animations
            card.style.animationDelay = `${(index % 12) * 0.1 + 0.1}s`;
        });
        
        // Trigger intersection observer for any new cards
        if (window.cardObserver) {
            cards.forEach(card => {
                window.cardObserver.observe(card);
            });
        }
    }
    
    dispatchViewChangeEvent(view) {
        const event = new CustomEvent('newsViewChanged', {
            detail: { view, previousView: this.currentView }
        });
        document.dispatchEvent(event);
    }
    
    trackViewChange(view) {
        // Analytics tracking if available
        if (typeof gtag !== 'undefined') {
            gtag('event', 'view_change', {
                'event_category': 'news_interface',
                'event_label': view,
                'value': 1
            });
        }
        
        console.log(`View changed to: ${view}`);
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
    
    // Public API methods
    getCurrentView() {
        return this.currentView;
    }
    
    isGridView() {
        return this.currentView === 'grid';
    }
    
    isListView() {
        return this.currentView === 'list';
    }
    
    // Method to programmatically set view
    setViewProgrammatically(view) {
        if (view === 'grid') {
            this.switchToGrid();
        } else if (view === 'list') {
            this.switchToList();
        }
    }
}

// Enhanced dropdown functionality that works with both views
function toggleDropdown(noticiaId) {
    // Close all other dropdowns
    document.querySelectorAll('.actions-menu').forEach(menu => {
        if (menu.id !== `dropdown-${noticiaId}`) {
            menu.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    const dropdown = document.getElementById(`dropdown-${noticiaId}`);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
        
        // Add ripple effect to trigger button
        const trigger = dropdown.previousElementSibling;
        if (trigger) {
            createRipple(trigger);
        }
    }
}

// Enhanced ripple effect
function createRipple(element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = '50%';
    ripple.style.top = '50%';
    ripple.style.transform = 'translate(-50%, -50%)';
    ripple.classList.add('ripple');
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.remove();
        }
    }, 600);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the view manager
    window.newsViewManager = new NewsViewManager();
    
    // Enhanced card interactions for both views
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    window.cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);
    
    // Observe all news cards
    document.querySelectorAll('.news-card').forEach(card => {
        window.cardObserver.observe(card);
        card.style.animationPlayState = 'paused';
    });
    
    // Enhanced keyboard navigation
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
        
        // Arrow key navigation for cards (works in both views)
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft' || 
            event.key === 'ArrowUp' || event.key === 'ArrowDown') {
            
            const focusedCard = document.activeElement.closest('.news-card');
            if (focusedCard) {
                const cards = Array.from(document.querySelectorAll('.news-card'));
                const currentIndex = cards.indexOf(focusedCard);
                let nextIndex;
                
                const isGridView = window.newsViewManager && window.newsViewManager.isGridView();
                
                if (isGridView) {
                    // Grid navigation
                    const gridColumns = getGridColumns();
                    
                    if (event.key === 'ArrowRight') {
                        nextIndex = (currentIndex + 1) % cards.length;
                    } else if (event.key === 'ArrowLeft') {
                        nextIndex = (currentIndex - 1 + cards.length) % cards.length;
                    } else if (event.key === 'ArrowDown') {
                        nextIndex = Math.min(currentIndex + gridColumns, cards.length - 1);
                    } else if (event.key === 'ArrowUp') {
                        nextIndex = Math.max(currentIndex - gridColumns, 0);
                    }
                } else {
                    // List navigation
                    if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
                        nextIndex = (currentIndex + 1) % cards.length;
                    } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
                        nextIndex = (currentIndex - 1 + cards.length) % cards.length;
                    }
                }
                
                if (nextIndex !== undefined && cards[nextIndex]) {
                    cards[nextIndex].focus();
                    event.preventDefault();
                }
            }
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.actions-dropdown')) {
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
    
    // Listen for view changes to update any dependent functionality
    document.addEventListener('newsViewChanged', function(event) {
        console.log('News view changed:', event.detail);
        
        // Update any other components that depend on the view
        // For example, update pagination or filters if needed
    });
});

// Utility function to calculate grid columns
function getGridColumns() {
    const container = document.querySelector('.news-cards-grid');
    if (!container) return 1;
    
    const containerWidth = container.offsetWidth;
    const cardWidth = 300; // Approximate card width
    const gap = 32; // Approximate gap
    
    return Math.floor((containerWidth + gap) / (cardWidth + gap)) || 1;
}

// Export for use in other scripts
window.NewsViewManager = NewsViewManager;
window.toggleDropdown = toggleDropdown;
window.createRipple = createRipple;