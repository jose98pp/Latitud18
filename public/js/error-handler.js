/**
 * Sistema de manejo de errores para UHTV
 */

// Configuración
const ERROR_CONFIG = {
    logToConsole: true,
    logToServer: false, // Cambiar a true si quieres enviar errores al servidor
    maxErrors: 10,
    ignoredErrors: [
        'Script error',
        'Non-Error promise rejection captured',
        'ResizeObserver loop limit exceeded',
        'unreachable code after return statement' // Errores de YouTube
    ]
};

let errorCount = 0;

/**
 * Filtrar errores que no son relevantes
 */
function shouldIgnoreError(message) {
    return ERROR_CONFIG.ignoredErrors.some(ignored => 
        message && message.includes(ignored)
    );
}

/**
 * Manejar errores JavaScript globales
 */
window.addEventListener('error', function(event) {
    if (errorCount >= ERROR_CONFIG.maxErrors) return;
    
    const message = event.message || 'Unknown error';
    
    if (shouldIgnoreError(message)) return;
    
    errorCount++;
    
    const errorInfo = {
        message: message,
        filename: event.filename || 'Unknown file',
        lineno: event.lineno || 0,
        colno: event.colno || 0,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        url: window.location.href
    };
    
    if (ERROR_CONFIG.logToConsole) {
        console.group('🚨 Error capturado por UHTV Error Handler');
        console.error('Mensaje:', errorInfo.message);
        console.error('Archivo:', errorInfo.filename);
        console.error('Línea:', errorInfo.lineno, 'Columna:', errorInfo.colno);
        console.error('URL:', errorInfo.url);
        console.groupEnd();
    }
    
    if (ERROR_CONFIG.logToServer) {
        sendErrorToServer(errorInfo);
    }
});

/**
 * Manejar promesas rechazadas
 */
window.addEventListener('unhandledrejection', function(event) {
    if (errorCount >= ERROR_CONFIG.maxErrors) return;
    
    const message = event.reason ? event.reason.toString() : 'Unhandled promise rejection';
    
    if (shouldIgnoreError(message)) return;
    
    errorCount++;
    
    if (ERROR_CONFIG.logToConsole) {
        console.group('🚨 Promise rechazada capturada por UHTV Error Handler');
        console.error('Razón:', event.reason);
        console.error('URL:', window.location.href);
        console.groupEnd();
    }
});

/**
 * Enviar error al servidor (opcional)
 */
function sendErrorToServer(errorInfo) {
    try {
        fetch('/api/log-error', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(errorInfo)
        }).catch(err => {
            console.warn('No se pudo enviar error al servidor:', err);
        });
    } catch (err) {
        console.warn('Error al enviar log al servidor:', err);
    }
}

/**
 * Función para reportar errores manualmente
 */
window.reportError = function(message, context = {}) {
    if (ERROR_CONFIG.logToConsole) {
        console.group('📝 Error reportado manualmente');
        console.error('Mensaje:', message);
        console.error('Contexto:', context);
        console.groupEnd();
    }
};

// Inicialización
console.log('✅ UHTV Error Handler inicializado');