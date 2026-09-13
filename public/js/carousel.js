/**
 * Carousel and Image Loading Optimization
 * Inspired by modern news websites like Brújula Digital
 */

document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading for images
    const lazyImages = document.querySelectorAll('img.lazy');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.getAttribute('data-src');
                
                if (src) {
                    img.src = src;
                    img.classList.remove('lazy');
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
    
    // Carousel auto-play with pause on hover
    const carouselEl = document.getElementById('heroCarousel') || document.getElementById('newsCarousel');
    if (carouselEl && typeof bootstrap !== 'undefined') {
        try {
            const bsCarousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
                interval: 5000,
                wrap: true,
                pause: 'hover',
                ride: 'carousel'
            });
            
            // Pause on focus for accessibility
            carouselEl.addEventListener('focusin', () => {
                if (bsCarousel && typeof bsCarousel.pause === 'function') {
                    bsCarousel.pause();
                }
            });
            
            carouselEl.addEventListener('focusout', () => {
                if (bsCarousel && typeof bsCarousel.cycle === 'function') {
                    bsCarousel.cycle();
                }
            });
        } catch (error) {
            console.warn('Error initializing carousel:', error);
        }
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading animation for images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (!img.complete) {
            img.style.opacity = '0';
            img.addEventListener('load', function() {
                this.style.transition = 'opacity 0.3s ease';
                this.style.opacity = '1';
            });
        }
    });
    
    // Mobile menu functionality (if exists)
    const hamburgerButton = document.getElementById('hamburgerButton');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMenu = document.getElementById('closeMenu');
    
    if (hamburgerButton && mobileMenu) {
        hamburgerButton.addEventListener('click', () => {
            mobileMenu.classList.remove('-translate-x-full');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (closeMenu && mobileMenu) {
        closeMenu.addEventListener('click', () => {
            mobileMenu.classList.add('-translate-x-full');
            document.body.style.overflow = '';
        });
    }
    
    // Close mobile menu when clicking outside
    if (mobileMenu) {
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !hamburgerButton.contains(e.target)) {
                mobileMenu.classList.add('-translate-x-full');
                document.body.style.overflow = '';
            }
        });
    }
});

// Error handling for images
function handleImageError(img) {
    // Evitar bucle infinito de errores
    if (img.src.includes('default-news.svg')) {
        console.warn('Default image also failed to load');
        return;
    }
    
    console.log('Image failed to load, using default:', img.src);
    img.src = '/images/default-news.svg';
    img.alt = 'Imagen no disponible - UHTV';
    img.classList.add('error-image');
    
    // Agregar un placeholder visual si es necesario
    const parent = img.parentElement;
    if (parent && parent.classList.contains('relative')) {
        parent.style.backgroundColor = '#f3f4f6';
    }
}

// Add error handlers to all images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            handleImageError(this);
        });
        
        // También manejar imágenes que ya están cargadas pero pueden tener errores
        if (img.complete && img.naturalHeight === 0) {
            handleImageError(img);
        }
    });
    
    // Observer para imágenes que se cargan dinámicamente
    const imageObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const newImages = node.querySelectorAll ? node.querySelectorAll('img') : [];
                    newImages.forEach(img => {
                        img.addEventListener('error', function() {
                            handleImageError(this);
                        });
                    });
                    
                    if (node.tagName === 'IMG') {
                        node.addEventListener('error', function() {
                            handleImageError(this);
                        });
                    }
                }
            });
        });
    });
    
    imageObserver.observe(document.body, {
        childList: true,
        subtree: true
    });
});