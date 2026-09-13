/**
 * Dynamic Filters with AJAX
 * Handles real-time filtering without page reloads
 */

class DynamicFilters {
    constructor() {
        this.form = document.getElementById('filters-form');
        this.searchInput = document.getElementById('search-input');
        this.categorySelect = document.getElementById('category-select');
        this.statusSelect = document.getElementById('status-select');
        this.newsContainer = document.getElementById('news-container');
        this.paginationContainer = document.getElementById('pagination-container');
        this.filtersContainer = document.getElementById('filters-container');
        
        this.debounceTimer = null;
        this.currentRequest = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateURL();
        this.updateActiveFilters();
    }
    
    bindEvents() {
        // Real-time search with debounce
        this.searchInput.addEventListener('input', (e) => {
            this.debounceFilter(500);
        });
        
        // Immediate filtering on select changes
        this.categorySelect.addEventListener('change', () => {
            this.performFilter();
        });
        
        this.statusSelect.addEventListener('change', () => {
            this.performFilter();
        });
        
        // Prevent form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performFilter();
        });
        
        // Handle pagination clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();
                const url = e.target.closest('.pagination a').href;
                const urlParams = new URL(url).searchParams;
                const page = urlParams.get('page');
                
                if (page) {
                    this.performFilter(page);
                }
            }
        });
    }
    
    debounceFilter(delay = 300) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.performFilter();
        }, delay);
    }
    
    async performFilter(page = 1) {
        // Cancel previous request if still pending
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        
        // Show loading state
        this.showLoading();
        
        // Prepare form data
        const formData = new FormData(this.form);
        formData.append('page', page);
        
        // Convert to URL parameters
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        try {
            // Create AbortController for this request
            const controller = new AbortController();
            this.currentRequest = controller;
            
            const response = await fetch(`${window.location.pathname}/filter?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: controller.signal
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Update content
                this.updateContent(data);
                
                // Update URL without page reload
                this.updateURL(params);
                
                // Update statistics if provided
                if (data.statistics) {
                    this.updateStatistics(data.statistics);
                }
                
                // Show results count
                this.showResultsCount(data.total_results);
                
                // Scroll to top of results
                this.scrollToResults();
            } else {
                throw new Error(data.message || 'Error al filtrar noticias');
            }
            
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error filtering news:', error);
                this.showError('Error al cargar las noticias. Por favor, intenta de nuevo.');
            }
        } finally {
            this.hideLoading();
            this.currentRequest = null;
        }
    }
    
    updateContent(data) {
        // Update news cards with fade effect
        this.newsContainer.style.opacity = '0.5';
        
        setTimeout(() => {
            // Update news cards
            const newsCardsContainer = this.newsContainer.querySelector('.news-cards-grid') || 
                                     this.newsContainer.querySelector('.text-center');
            
            if (newsCardsContainer) {
                newsCardsContainer.outerHTML = data.html;
            } else {
                this.newsContainer.innerHTML = data.html + 
                    '<div id="pagination-container">' + data.pagination + '</div>';
            }
            
            // Update pagination
            this.paginationContainer.innerHTML = data.pagination;
            
            // Restore opacity with animation
            this.newsContainer.style.opacity = '1';
            
            // Re-initialize card animations
            this.initializeCardAnimations();
            
            // Update active filters display
            this.updateActiveFilters();
            
        }, 150);
    }
    
    updateStatistics(stats) {
        // Update filtered statistics in the stats bar
        const statNumbers = document.querySelectorAll('.stat-number');
        
        if (statNumbers.length >= 3) {
            // Update total, published, and drafts
            this.animateStatUpdate(statNumbers[0], stats.total);
            this.animateStatUpdate(statNumbers[1], stats.published);
            this.animateStatUpdate(statNumbers[2], stats.drafts);
            
            // Update percentage
            const percentageElement = document.querySelector('.stat-percentage');
            if (percentageElement) {
                percentageElement.textContent = `${stats.published_percentage}% del total`;
            }
        }
    }
    
    animateStatUpdate(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        const increment = (newValue - currentValue) / 20;
        let current = currentValue;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= newValue) || (increment < 0 && current <= newValue)) {
                element.textContent = newValue;
                clearInterval(timer);
            } else {
                element.textContent = Math.round(current);
            }
        }, 25);
    }
    
    showResultsCount(count) {
        // Remove existing results count
        const existingCount = document.querySelector('.results-count');
        if (existingCount) {
            existingCount.remove();
        }
        
        // Add new results count
        const countElement = document.createElement('div');
        countElement.className = 'results-count text-sm text-gray-600 mb-4';
        countElement.innerHTML = `
            <i class="fas fa-info-circle mr-1"></i>
            Se encontraron <strong>${count}</strong> resultado${count !== 1 ? 's' : ''}
        `;
        
        this.newsContainer.insertBefore(countElement, this.newsContainer.firstChild);
    }
    
    updateURL(params) {
        const url = new URL(window.location);
        
        // Clear existing search params
        url.search = '';
        
        // Add new params
        if (params) {
            params.forEach((value, key) => {
                if (key !== 'page' || value !== '1') {
                    url.searchParams.set(key, value);
                }
            });
        }
        
        // Update URL without page reload
        window.history.replaceState({}, '', url.toString());
    }
    
    showLoading() {
        this.filtersContainer.classList.add('filters-loading');
        this.newsContainer.style.pointerEvents = 'none';
        this.newsContainer.style.opacity = '0.7';
    }
    
    hideLoading() {
        this.filtersContainer.classList.remove('filters-loading');
        this.newsContainer.style.pointerEvents = 'auto';
        this.newsContainer.style.opacity = '1';
    }
    
    showError(message) {
        // Remove existing error
        const existingError = document.querySelector('.filter-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Show error message
        const errorElement = document.createElement('div');
        errorElement.className = 'filter-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorElement.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        this.newsContainer.insertBefore(errorElement, this.newsContainer.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorElement.parentNode) {
                errorElement.remove();
            }
        }, 5000);
    }
    
    scrollToResults() {
        const offset = this.newsContainer.getBoundingClientRect().top + window.pageYOffset - 100;
        window.scrollTo({
            top: offset,
            behavior: 'smooth'
        });
    }
    
    initializeCardAnimations() {
        // Re-initialize entrance animations for new cards
        const cards = document.querySelectorAll('.news-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${(index % 8) * 0.1}s`;
            card.classList.add('animate-entrance');
        });
        
        // Remove animation class after animation completes
        setTimeout(() => {
            cards.forEach(card => {
                card.classList.remove('animate-entrance');
            });
        }, 1000);
    }
    
    updateActiveFilters() {
        const hasSearch = this.searchInput.value.trim() !== '';
        const hasCategory = this.categorySelect.selectedIndex > 0;
        const hasStatus = this.statusSelect.selectedIndex > 0;
        
        const activeCount = [hasSearch, hasCategory, hasStatus].filter(Boolean).length;
        
        // Update clear button state
        const clearBtn = document.querySelector('.clear-filters-btn');
        if (clearBtn) {
            if (activeCount > 0) {
                clearBtn.classList.add('has-filters');
                
                // Update or add count badge
                let countBadge = clearBtn.querySelector('.filter-count');
                if (!countBadge) {
                    countBadge = document.createElement('span');
                    countBadge.className = 'filter-count';
                    clearBtn.appendChild(countBadge);
                }
                countBadge.textContent = activeCount;
            } else {
                clearBtn.classList.remove('has-filters');
                const countBadge = clearBtn.querySelector('.filter-count');
                if (countBadge) {
                    countBadge.remove();
                }
            }
        }
        
        // Update active filters section
        this.updateActiveFiltersSection(hasSearch, hasCategory, hasStatus, activeCount);
    }
    
    updateActiveFiltersSection(hasSearch, hasCategory, hasStatus, activeCount) {
        let activeFiltersSection = document.getElementById('active-filters');
        
        if (activeCount > 0) {
            if (!activeFiltersSection) {
                // Create active filters section
                activeFiltersSection = document.createElement('div');
                activeFiltersSection.id = 'active-filters';
                activeFiltersSection.className = 'active-filters';
                this.form.appendChild(activeFiltersSection);
            }
            
            // Build filter tags HTML
            let tagsHTML = '';
            
            if (hasSearch) {
                const searchValue = this.searchInput.value.substring(0, 20) + (this.searchInput.value.length > 20 ? '...' : '');
                tagsHTML += `
                    <span class="filter-tag">
                        <i class="fas fa-search mr-1"></i>
                        "${searchValue}"
                        <button type="button" class="remove-tag" onclick="removeFilter('search')" title="Quitar filtro de búsqueda">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
            }
            
            if (hasCategory) {
                const categoryText = this.categorySelect.options[this.categorySelect.selectedIndex].text;
                tagsHTML += `
                    <span class="filter-tag">
                        <i class="fas fa-tag mr-1"></i>
                        ${categoryText}
                        <button type="button" class="remove-tag" onclick="removeFilter('category')" title="Quitar filtro de categoría">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
            }
            
            if (hasStatus) {
                const statusText = this.statusSelect.options[this.statusSelect.selectedIndex].text;
                const statusIcon = this.statusSelect.value === '1' ? 'check-circle' : 'edit';
                tagsHTML += `
                    <span class="filter-tag">
                        <i class="fas fa-${statusIcon} mr-1"></i>
                        ${statusText}
                        <button type="button" class="remove-tag" onclick="removeFilter('status')" title="Quitar filtro de estado">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
            }
            
            activeFiltersSection.innerHTML = `
                <div class="active-filters-title">
                    <i class="fas fa-filter"></i>
                    Filtros activos
                    <span class="filter-count">${activeCount}</span>
                </div>
                <div class="filter-tags">
                    ${tagsHTML}
                    <button type="button" class="clear-all-filters" onclick="clearAllFilters()" title="Limpiar todos los filtros">
                        <i class="fas fa-times-circle"></i>
                        Limpiar todo
                    </button>
                </div>
            `;
        } else if (activeFiltersSection) {
            // Remove active filters section with animation
            activeFiltersSection.style.animation = 'slideUp 0.3s ease-out';
            setTimeout(() => {
                if (activeFiltersSection.parentNode) {
                    activeFiltersSection.remove();
                }
            }, 300);
        }
    }

    // Public methods for external use
    clearSearch() {
        this.searchInput.value = '';
        const searchWrapper = this.searchInput.parentElement;
        searchWrapper.classList.remove('has-value');
        this.performFilter();
    }
    
    clearAllFilters() {
        this.searchInput.value = '';
        this.categorySelect.selectedIndex = 0;
        this.statusSelect.selectedIndex = 0;
        
        const searchWrapper = this.searchInput.parentElement;
        searchWrapper.classList.remove('has-value');
        
        this.performFilter();
    }
    
    removeFilter(filterName) {
        const input = this.form.querySelector(`[name="${filterName}"]`);
        
        if (input) {
            if (input.type === 'text') {
                input.value = '';
                if (filterName === 'search') {
                    const searchWrapper = input.parentElement;
                    searchWrapper.classList.remove('has-value');
                }
            } else if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            }
            
            this.performFilter();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dynamicFilters = new DynamicFilters();
});

// Global functions for backward compatibility
function clearSearch() {
    if (window.dynamicFilters) {
        window.dynamicFilters.clearSearch();
    }
}

function clearAllFilters() {
    if (window.dynamicFilters) {
        window.dynamicFilters.clearAllFilters();
    }
}

function removeFilter(filterName) {
    if (window.dynamicFilters) {
        window.dynamicFilters.removeFilter(filterName);
    }
}

// Add CSS for entrance animation
const style = document.createElement('style');
style.textContent = `
    .animate-entrance {
        animation: slideInUp 0.6s ease-out both;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);