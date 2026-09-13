/**
 * Dark Mode Toggle Functionality - Enhanced
 * Maneja el cambio entre modo claro y oscuro con persistencia mejorada
 */

// Variables globales
let darkModeEnabled = false;
const DARK_MODE_KEY = 'uhtv-dark-mode';

// Función para aplicar modo oscuro
function applyDarkMode(isDark) {
    const html = document.documentElement;
    const body = document.body;
    
    if (isDark) {
        html.classList.add('dark');
        body.classList.add('dark');
        html.style.colorScheme = 'dark';
        html.setAttribute('data-bs-theme', 'dark');
        darkModeEnabled = true;
        
        // Forzar actualización de meta theme-color
        updateThemeColor('#1f2937');
    } else {
        html.classList.remove('dark');
        body.classList.remove('dark');
        html.style.colorScheme = 'light';
        html.setAttribute('data-bs-theme', 'light');
        darkModeEnabled = false;
        
        // Forzar actualización de meta theme-color
        updateThemeColor('#7c3aed');
    }
    
    updateToggleButtons(isDark);
    
    // Disparar evento personalizado para otros scripts
    window.dispatchEvent(new CustomEvent('darkModeChanged', { 
        detail: { isDark: isDark } 
    }));
}

// Función para alternar modo oscuro
function toggleDarkMode() {
    const newState = !darkModeEnabled;
    applyDarkMode(newState);
    localStorage.setItem(DARK_MODE_KEY, newState ? 'dark' : 'light');
}

// Función para actualizar botones
function updateToggleButtons(isDark) {
    const toggleButtons = document.querySelectorAll('[data-dark-mode-toggle]');
    
    toggleButtons.forEach(button => {
        const sunIcon = button.querySelector('.sun-icon');
        const moonIcon = button.querySelector('.moon-icon');
        const sunText = button.querySelector('.sun-text');
        const moonText = button.querySelector('.moon-text');
        
        if (sunIcon && moonIcon) {
            if (isDark) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
                if (sunText) sunText.classList.remove('hidden');
                if (moonText) moonText.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
                if (sunText) sunText.classList.add('hidden');
                if (moonText) moonText.classList.remove('hidden');
            }
        }
    });
}

// Función para actualizar color del tema
function updateThemeColor(color) {
    let themeColorMeta = document.querySelector('meta[name="theme-color"]');
    if (themeColorMeta) {
        themeColorMeta.setAttribute('content', color);
    }
}

// Inicialización mejorada
function initDarkMode() {
    try {
        // Verificar preferencia guardada o del sistema
        const savedTheme = localStorage.getItem(DARK_MODE_KEY);
        const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        const shouldBeDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark);
        
        // Aplicar inmediatamente para evitar flash
        applyDarkMode(shouldBeDark);
        
        // Configurar event listeners para botones (delegación de eventos)
        document.addEventListener('click', function(e) {
            const toggleButton = e.target.closest('[data-dark-mode-toggle]');
            if (toggleButton) {
                e.preventDefault();
                e.stopPropagation();
                toggleDarkMode();
            }
        });
        
        // Escuchar cambios en preferencias del sistema
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                // Solo aplicar si no hay preferencia guardada
                if (!localStorage.getItem(DARK_MODE_KEY)) {
                    applyDarkMode(e.matches);
                }
            });
        }
        
        // Verificar estado cada vez que la página se vuelve visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                const currentSavedTheme = localStorage.getItem(DARK_MODE_KEY);
                const currentSystemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const currentShouldBeDark = currentSavedTheme === 'dark' || (!currentSavedTheme && currentSystemPrefersDark);
                
                if (currentShouldBeDark !== darkModeEnabled) {
                    applyDarkMode(currentShouldBeDark);
                }
            }
        });
        
        console.log('Dark mode initialized successfully. Current state:', darkModeEnabled ? 'dark' : 'light');
    } catch (error) {
        console.error('Error initializing dark mode:', error);
    }
}

// Inicializar inmediatamente y cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDarkMode);
} else {
    initDarkMode();
}

// También exponer funciones globales para compatibilidad
window.toggleDarkMode = toggleDarkMode;
window.applyDarkMode = applyDarkMode;
window.darkModeEnabled = () => darkModeEnabled;

// Aplicar modo oscuro inmediatamente si es necesario (para evitar flash)
(function() {
    try {
        const savedTheme = localStorage.getItem(DARK_MODE_KEY);
        const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    } catch (e) {
        console.warn('Error in immediate dark mode application:', e);
    }
})();