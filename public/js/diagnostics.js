/**
 * Diagnósticos del Sistema UHTV
 * Verifica el funcionamiento de componentes críticos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Solo ejecutar diagnósticos en desarrollo
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        runDiagnostics();
    }
});

function runDiagnostics() {
    console.group('🔍 UHTV System Diagnostics');
    
    // Verificar modo oscuro
    checkDarkMode();
    
    // Verificar imágenes
    checkImages();
    
    // Verificar carrusel
    checkCarousel();
    
    // Verificar búsqueda
    checkSearch();
    
    console.groupEnd();
}

function checkDarkMode() {
    console.group('🌙 Dark Mode Check');
    
    const darkModeButtons = document.querySelectorAll('[data-dark-mode-toggle]');
    console.log(`Found ${darkModeButtons.length} dark mode toggle buttons`);
    
    const isDarkMode = document.documentElement.classList.contains('dark');
    console.log(`Current mode: ${isDarkMode ? 'Dark' : 'Light'}`);
    
    const savedTheme = localStorage.getItem('uhtv-dark-mode');
    console.log(`Saved theme preference: ${savedTheme || 'None'}`);
    
    if (darkModeButtons.length === 0) {
        console.warn('⚠️ No dark mode toggle buttons found');
    } else {
        console.log('✅ Dark mode buttons found');
    }
    
    console.groupEnd();
}

function checkImages() {
    console.group('🖼️ Images Check');
    
    const images = document.querySelectorAll('img');
    let brokenImages = 0;
    let loadedImages = 0;
    
    images.forEach((img, index) => {
        if (img.complete) {
            if (img.naturalHeight === 0) {
                brokenImages++;
                console.warn(`❌ Broken image ${index + 1}: ${img.src}`);
            } else {
                loadedImages++;
            }
        } else {
            img.addEventListener('load', () => {
                console.log(`✅ Image loaded: ${img.src}`);
            });
            img.addEventListener('error', () => {
                console.error(`❌ Image failed to load: ${img.src}`);
            });
        }
    });
    
    console.log(`Total images: ${images.length}`);
    console.log(`Loaded images: ${loadedImages}`);
    console.log(`Broken images: ${brokenImages}`);
    
    // Verificar imagen por defecto
    checkDefaultImage().then(exists => {
        console.log(`Default image available: ${exists ? '✅' : '❌'}`);
    });
    
    console.groupEnd();
}

function checkDefaultImage() {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => resolve(true);
        img.onerror = () => resolve(false);
        img.src = '/images/default-news.svg';
        
        // Si ya está cargada
        if (img.complete) {
            resolve(img.naturalHeight !== 0);
        }
    });
}

function checkCarousel() {
    console.group('🎠 Carousel Check');
    
    const carousel = document.getElementById('newsCarousel');
    if (carousel) {
        console.log('✅ News carousel found');
        
        const carouselItems = carousel.querySelectorAll('.carousel-item');
        console.log(`Carousel items: ${carouselItems.length}`);
        
        const indicators = carousel.querySelectorAll('.carousel-indicators button');
        console.log(`Carousel indicators: ${indicators.length}`);
        
        if (typeof bootstrap !== 'undefined') {
            console.log('✅ Bootstrap is loaded');
        } else {
            console.warn('⚠️ Bootstrap not found');
        }
    } else {
        console.warn('⚠️ News carousel not found');
    }
    
    console.groupEnd();
}

function checkSearch() {
    console.group('🔍 Search Check');
    
    const searchForms = document.querySelectorAll('form[action*="buscar"]');
    console.log(`Search forms found: ${searchForms.length}`);
    
    const searchInputs = document.querySelectorAll('input[name="q"]');
    console.log(`Search inputs found: ${searchInputs.length}`);
    
    if (searchForms.length > 0) {
        console.log('✅ Search functionality available');
    } else {
        console.warn('⚠️ Search forms not found');
    }
    
    console.groupEnd();
}

// Función para reportar errores
window.addEventListener('error', function(e) {
    console.error('🚨 JavaScript Error:', {
        message: e.message,
        filename: e.filename,
        lineno: e.lineno,
        colno: e.colno,
        error: e.error
    });
});

// Función para reportar errores de promesas no capturadas
window.addEventListener('unhandledrejection', function(e) {
    console.error('🚨 Unhandled Promise Rejection:', e.reason);
});

// Exportar funciones para uso manual
window.UHTVDiagnostics = {
    runDiagnostics,
    checkDarkMode,
    checkImages,
    checkCarousel,
    checkSearch
};