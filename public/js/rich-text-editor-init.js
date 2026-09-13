/**
 * Rich Text Editor Initialization with Enhanced Error Handling and Performance Optimization
 * This script provides robust initialization, fallback mechanisms, and lazy loading for the Rich Text Editor
 */

class RichTextEditorManager {
    constructor() {
        this.retryAttempts = 0;
        this.maxRetries = 3;
        this.retryDelay = 1000; // Start with 1 second
        this.fallbackActivated = false;
        this.librariesLoaded = false;
        this.loadingPromise = null;
        this.performanceMetrics = {
            startTime: null,
            libraryLoadTime: null,
            editorInitTime: null
        };
    }

    /**
     * Initialize the Rich Text Editor with error handling, fallback, and performance optimization
     */
    async initializeEditor(config) {
        const {
            containerId,
            hiddenInputId,
            validationErrorId = null,
            initialContent = '',
            onChange = null,
            onAutoSave = null,
            required = false,
            placeholder = 'Escriba su contenido aquí...',
            minHeight = '200px',
            lazyLoad = false // Deshabilitado por defecto para mayor estabilidad
        } = config;

        this.performanceMetrics.startTime = performance.now();

        try {
            // Show loading indicator
            this.showLoadingIndicator(containerId);

            // Primero intentar con librerías ya cargadas
            if (typeof React !== 'undefined' && typeof ReactDOM !== 'undefined' && typeof RichTextEditor !== 'undefined') {
                console.log('Using pre-loaded libraries');
                this.librariesLoaded = true;
            } else if (lazyLoad && !this.librariesLoaded) {
                console.log('Attempting lazy load...');
                await this.lazyLoadLibraries();
            } else {
                console.log('Waiting for dependencies...');
                await this.waitForDependencies();
            }

            this.performanceMetrics.libraryLoadTime = performance.now();

            await this.renderEditor(config);
            this.setupFormValidation(config);
            this.setupKeyboardShortcuts(config);
            
            this.performanceMetrics.editorInitTime = performance.now();
            
            console.log('Rich Text Editor initialized successfully');
            console.log('Performance metrics:', {
                totalTime: (this.performanceMetrics.editorInitTime - this.performanceMetrics.startTime).toFixed(2) + 'ms',
                libraryLoadTime: (this.performanceMetrics.libraryLoadTime - this.performanceMetrics.startTime).toFixed(2) + 'ms',
                editorInitTime: (this.performanceMetrics.editorInitTime - this.performanceMetrics.libraryLoadTime).toFixed(2) + 'ms'
            });
        } catch (error) {
            console.error('Failed to initialize Rich Text Editor:', error);
            this.activateFallback(config);
        }
    }

    /**
     * Lazy load React libraries for better performance
     */
    async lazyLoadLibraries() {
        if (this.loadingPromise) {
            return this.loadingPromise;
        }

        this.loadingPromise = new Promise(async (resolve, reject) => {
            try {
                // Check if libraries are already loaded
                if (typeof React !== 'undefined' && typeof ReactDOM !== 'undefined' && typeof RichTextEditor !== 'undefined') {
                    this.librariesLoaded = true;
                    resolve();
                    return;
                }

                // Load React
                if (typeof React === 'undefined') {
                    await this.loadScript('https://unpkg.com/react@17/umd/react.production.min.js');
                }

                // Load ReactDOM
                if (typeof ReactDOM === 'undefined') {
                    await this.loadScript('https://unpkg.com/react-dom@17/umd/react-dom.production.min.js');
                }

                // Load Rich Text Editor
                if (typeof RichTextEditor === 'undefined') {
                    await this.loadScript('/js/rich-text-editor.js');
                }

                this.librariesLoaded = true;
                resolve();
            } catch (error) {
                reject(error);
            }
        });

        return this.loadingPromise;
    }

    /**
     * Load a script dynamically
     */
    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.crossOrigin = 'anonymous';
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            document.head.appendChild(script);
        });
    }

    /**
     * Wait for React dependencies to load with timeout
     */
    async waitForDependencies(timeout = 10000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            const checkDependencies = () => {
                if (typeof React !== 'undefined' && typeof ReactDOM !== 'undefined' && typeof RichTextEditor !== 'undefined') {
                    resolve();
                    return;
                }

                if (Date.now() - startTime > timeout) {
                    reject(new Error('Timeout waiting for dependencies'));
                    return;
                }

                setTimeout(checkDependencies, 100);
            };

            checkDependencies();
        });
    }

    /**
     * Show loading indicator while editor initializes
     */
    showLoadingIndicator(containerId) {
        const editorContainer = document.getElementById(containerId);
        if (!editorContainer) return;

        editorContainer.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando editor...</span>
                    </div>
                    <div class="mt-2 text-muted">Cargando editor de texto...</div>
                </div>
            </div>
        `;
    }

    /**
     * Render the React Rich Text Editor with optimizations
     */
    async renderEditor(config) {
        const { containerId, hiddenInputId, initialContent, onChange, onAutoSave, useWordStyle = false } = config;
        
        const editorContainer = document.getElementById(containerId);
        const hiddenInput = document.getElementById(hiddenInputId);

        if (!editorContainer || !hiddenInput) {
            throw new Error('Required DOM elements not found');
        }

        // Decode HTML entities in initial content
        let decodedContent = initialContent;
        if (decodedContent) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = decodedContent;
            decodedContent = tempDiv.innerHTML;
        }

        // Clear loading indicator
        editorContainer.innerHTML = '';

        // Choose editor component based on configuration
        const EditorComponent = useWordStyle && typeof WordStyleEditor !== 'undefined' ? WordStyleEditor : RichTextEditor;
        
        // Render React component with performance optimizations
        ReactDOM.render(
            React.createElement(EditorComponent, {
                initialContent: decodedContent,
                onChange: (content) => {
                    hiddenInput.value = content;
                    if (onChange) onChange(content);
                    this.clearValidationError(config.validationErrorId);
                },
                onAutoSave: (content) => {
                    if (onAutoSave) onAutoSave(content);
                    console.log('Auto-saved at:', new Date().toLocaleTimeString());
                }
            }),
            editorContainer
        );

        // Add performance observer for editor interactions
        this.observeEditorPerformance(editorContainer);
    }

    /**
     * Setup form validation and submission handling with enhanced UX
     */
    setupFormValidation(config) {
        const { containerId, hiddenInputId, validationErrorId, required } = config;
        const form = document.querySelector('form');
        
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const editorContainer = document.getElementById(containerId);
            const hiddenInput = document.getElementById(hiddenInputId);
            const editorContent = editorContainer?.querySelector('[contenteditable]');

            // Update hidden input with current editor content
            if (editorContent && !this.fallbackActivated) {
                hiddenInput.value = editorContent.innerHTML;
            }

            // Validate required content
            if (required) {
                const textContent = hiddenInput.value.replace(/<[^>]*>/g, '').trim();
                if (!textContent) {
                    e.preventDefault();
                    this.showValidationError(validationErrorId, 'El contenido es requerido.');
                    editorContent?.focus();
                    return false;
                }
            }

            // Show enhanced loading state
            this.showEnhancedLoadingState(e.target);
        });
    }

    /**
     * Setup keyboard shortcuts for power users
     */
    setupKeyboardShortcuts(config) {
        const { containerId } = config;
        const editorContainer = document.getElementById(containerId);
        
        if (!editorContainer) return;

        // Add keyboard shortcuts help tooltip
        this.addKeyboardShortcutsHelp(editorContainer);

        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Only activate when editor is focused
            const activeElement = document.activeElement;
            const isEditorFocused = editorContainer.contains(activeElement);
            
            if (!isEditorFocused) return;

            // Ctrl/Cmd + S for save (prevent default browser save)
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const form = document.querySelector('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                }
            }

            // Ctrl/Cmd + Shift + P for preview toggle
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                const previewBtn = editorContainer.querySelector('[title="Vista previa"]');
                if (previewBtn) {
                    previewBtn.click();
                }
            }
        });
    }

    /**
     * Add help tooltips and keyboard shortcuts help
     */
    addKeyboardShortcutsHelp(editorContainer) {
        // Create help container
        const helpContainer = document.createElement('div');
        helpContainer.className = 'position-absolute d-flex gap-2';
        helpContainer.style.cssText = 'top: 10px; right: 10px; z-index: 1000;';

        // Keyboard shortcuts button
        const keyboardButton = document.createElement('button');
        keyboardButton.type = 'button';
        keyboardButton.className = 'btn btn-sm btn-outline-secondary';
        keyboardButton.innerHTML = '<i class="fas fa-keyboard"></i>';
        keyboardButton.title = 'Atajos de teclado';
        keyboardButton.addEventListener('click', () => {
            this.showKeyboardShortcutsModal();
        });

        // General help button
        const helpButton = document.createElement('button');
        helpButton.type = 'button';
        helpButton.className = 'btn btn-sm btn-outline-info';
        helpButton.innerHTML = '<i class="fas fa-question-circle"></i>';
        helpButton.title = 'Ayuda del editor';
        helpButton.addEventListener('click', () => {
            this.showHelpModal();
        });

        helpContainer.appendChild(keyboardButton);
        helpContainer.appendChild(helpButton);

        editorContainer.style.position = 'relative';
        editorContainer.appendChild(helpContainer);

        // Add tooltips to toolbar buttons
        this.addToolbarTooltips(editorContainer);
    }

    /**
     * Add enhanced tooltips to toolbar buttons
     */
    addToolbarTooltips(editorContainer) {
        // Wait for editor to render, then add tooltips
        setTimeout(() => {
            const toolbar = editorContainer.querySelector('[role="toolbar"]');
            if (!toolbar) return;

            const tooltipData = {
                'Deshacer': 'Deshace el último cambio realizado (Ctrl+Z)',
                'Rehacer': 'Rehace el último cambio deshecho (Ctrl+Y)',
                'Negrita': 'Aplica formato en negrita al texto seleccionado (Ctrl+B)',
                'Cursiva': 'Aplica formato en cursiva al texto seleccionado (Ctrl+I)',
                'Subrayado': 'Subraya el texto seleccionado (Ctrl+U)',
                'Tachado': 'Aplica formato tachado al texto seleccionado',
                'Color de texto': 'Cambia el color del texto seleccionado',
                'Alinear a la izquierda': 'Alinea el párrafo a la izquierda',
                'Centrar': 'Centra el párrafo',
                'Alinear a la derecha': 'Alinea el párrafo a la derecha',
                'Justificar': 'Justifica el párrafo',
                'Lista con viñetas': 'Crea una lista con viñetas',
                'Lista numerada': 'Crea una lista numerada',
                'Disminuir sangría': 'Reduce la sangría del párrafo',
                'Aumentar sangría': 'Aumenta la sangría del párrafo',
                'Subir imagen': 'Sube e inserta una imagen (máx. 5MB, JPG/PNG/GIF/WebP)',
                'Vista previa': 'Alterna entre modo edición y vista previa'
            };

            // Add enhanced tooltips
            toolbar.querySelectorAll('button, label').forEach(element => {
                const title = element.getAttribute('title');
                if (title && tooltipData[title]) {
                    element.setAttribute('title', tooltipData[title]);
                    element.setAttribute('data-bs-toggle', 'tooltip');
                    element.setAttribute('data-bs-placement', 'bottom');
                }
            });

            // Initialize Bootstrap tooltips if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                toolbar.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
                    new bootstrap.Tooltip(element);
                });
            }
        }, 1000);
    }

    /**
     * Show keyboard shortcuts modal
     */
    showKeyboardShortcutsModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-keyboard me-2"></i>Atajos de Teclado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-font me-2"></i>Formato de Texto</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>B</kbd> - Negrita</li>
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>I</kbd> - Cursiva</li>
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>U</kbd> - Subrayado</li>
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>Z</kbd> - Deshacer</li>
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>Y</kbd> - Rehacer</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-cogs me-2"></i>Acciones</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>S</kbd> - Guardar</li>
                                    <li class="mb-2"><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd> - Vista previa</li>
                                    <li class="mb-2"><kbd>Enter</kbd> - Nuevo párrafo</li>
                                    <li class="mb-2"><kbd>Shift</kbd> + <kbd>Enter</kbd> - Salto de línea</li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Consejo:</strong> Estos atajos funcionan cuando el cursor está dentro del área de edición.
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.showModal(modal);
    }

    /**
     * Show general help modal
     */
    showHelpModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Ayuda del Editor de Texto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-image me-2"></i>Subida de Imágenes</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Formatos:</strong> JPG, PNG, GIF, WebP</li>
                                    <li><strong>Tamaño máximo:</strong> 5 MB</li>
                                    <li><strong>Recomendación:</strong> Use imágenes optimizadas</li>
                                </ul>
                                
                                <h6 class="mt-4"><i class="fas fa-palette me-2"></i>Formato de Texto</h6>
                                <ul class="list-unstyled">
                                    <li>• Seleccione texto antes de aplicar formato</li>
                                    <li>• Use la paleta de colores predefinida</li>
                                    <li>• Combine formatos para mayor impacto</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-list me-2"></i>Listas y Estructura</h6>
                                <ul class="list-unstyled">
                                    <li>• Presione Enter para nuevo elemento</li>
                                    <li>• Presione Enter dos veces para finalizar</li>
                                    <li>• Use sangría para sublistas</li>
                                </ul>
                                
                                <h6 class="mt-4"><i class="fas fa-save me-2"></i>Guardado</h6>
                                <ul class="list-unstyled">
                                    <li>• Guardado automático cada 3 segundos</li>
                                    <li>• Use Ctrl+S para guardar manualmente</li>
                                    <li>• Siempre guarde antes de salir</li>
                                </ul>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-12">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Solución de Problemas Comunes</h6>
                                <div class="accordion" id="troubleshootingAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem1">
                                                El editor no carga correctamente
                                            </button>
                                        </h2>
                                        <div id="problem1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                            <div class="accordion-body">
                                                <strong>Soluciones:</strong>
                                                <ul>
                                                    <li>Actualice la página (F5)</li>
                                                    <li>Verifique su conexión a internet</li>
                                                    <li>Desactive bloqueadores de anuncios temporalmente</li>
                                                    <li>Intente en modo incógnito</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem2">
                                                Error al subir imágenes
                                            </button>
                                        </h2>
                                        <div id="problem2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                            <div class="accordion-body">
                                                <strong>Verificar:</strong>
                                                <ul>
                                                    <li>Tamaño de archivo (máx. 5 MB)</li>
                                                    <li>Formato de imagen (JPG, PNG, GIF, WebP)</li>
                                                    <li>Conexión a internet estable</li>
                                                    <li>Espacio disponible en el servidor</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#problem3">
                                                El formato se pierde al guardar
                                            </button>
                                        </h2>
                                        <div id="problem3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                            <div class="accordion-body">
                                                <strong>Evitar:</strong>
                                                <ul>
                                                    <li>Copiar y pegar desde Word</li>
                                                    <li>Usar HTML personalizado</li>
                                                    <li>Estilos CSS complejos</li>
                                                </ul>
                                                <strong>Usar:</strong> Solo las herramientas del editor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Consejo:</strong> Para obtener ayuda adicional, consulte la documentación completa o contacte al administrador del sistema.
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.showModal(modal);
    }

    /**
     * Generic modal display function
     */
    showModal(modal) {
        document.body.appendChild(modal);
        
        // Initialize Bootstrap modal if available
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        } else {
            // Fallback for when Bootstrap JS is not available
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            
            const closeBtn = modal.querySelector('.btn-close');
            const closeHandler = () => {
                document.body.removeChild(modal);
            };
            
            closeBtn.addEventListener('click', closeHandler);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeHandler();
                }
            });
        }
    }

    /**
     * Observe editor performance for optimization insights
     */
    observeEditorPerformance(editorContainer) {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    if (entry.duration > 100) { // Log slow operations
                        console.warn(`Slow editor operation detected: ${entry.name} took ${entry.duration.toFixed(2)}ms`);
                    }
                });
            });

            try {
                observer.observe({ entryTypes: ['measure', 'navigation'] });
            } catch (e) {
                console.log('Performance observer not fully supported');
            }
        }
    }

    /**
     * Activate fallback textarea when Rich Text Editor fails
     */
    activateFallback(config) {
        const { containerId, hiddenInputId, placeholder, minHeight, required } = config;
        const editorContainer = document.getElementById(containerId);
        const hiddenInput = document.getElementById(hiddenInputId);

        if (!editorContainer || !hiddenInput) return;

        this.fallbackActivated = true;

        // Create fallback notice
        const notice = document.createElement('div');
        notice.className = 'alert alert-warning mb-2';
        notice.innerHTML = '<i class="fas fa-exclamation-triangle"></i> El editor avanzado no está disponible. Usando editor básico.';

        // Create fallback textarea
        const textarea = document.createElement('textarea');
        textarea.className = 'form-control';
        textarea.name = hiddenInputId.replace('-hidden', '_fallback');
        textarea.value = hiddenInput.value;
        textarea.placeholder = placeholder;
        textarea.rows = 8;
        textarea.style.minHeight = minHeight;
        if (required) textarea.required = true;

        // Update hidden input when textarea changes
        textarea.addEventListener('input', () => {
            hiddenInput.value = textarea.value;
        });

        // Replace editor container content
        editorContainer.innerHTML = '';
        editorContainer.appendChild(notice);
        editorContainer.appendChild(textarea);

        console.log('Fallback editor activated');
    }

    /**
     * Show validation error message
     */
    showValidationError(validationErrorId, message) {
        if (!validationErrorId) return;
        
        const errorElement = document.getElementById(validationErrorId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    /**
     * Clear validation error message
     */
    clearValidationError(validationErrorId) {
        if (!validationErrorId) return;
        
        const errorElement = document.getElementById(validationErrorId);
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    /**
     * Show enhanced loading state on form submission with progress indication
     */
    showEnhancedLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            
            // Create progress indicator
            const progressContainer = document.createElement('div');
            progressContainer.className = 'mt-2';
            progressContainer.innerHTML = `
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted">Procesando contenido...</small>
            `;

            // Update button text
            if (originalText.includes('Crear')) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            } else if (originalText.includes('Actualizar') || originalText.includes('Guardar')) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            }

            // Add progress indicator after button
            submitBtn.parentNode.insertBefore(progressContainer, submitBtn.nextSibling);

            // Animate progress bar
            const progressBar = progressContainer.querySelector('.progress-bar');
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
            }, 200);

            // Clean up on page unload
            window.addEventListener('beforeunload', () => {
                clearInterval(interval);
            });
        }
    }

    /**
     * Show loading state on form submission (legacy method for compatibility)
     */
    showLoadingState(form) {
        this.showEnhancedLoadingState(form);
    }

    /**
     * Retry mechanism for failed initialization
     */
    async retryInitialization(config) {
        if (this.retryAttempts >= this.maxRetries) {
            throw new Error('Maximum retry attempts reached');
        }

        this.retryAttempts++;
        console.log(`Retrying editor initialization (attempt ${this.retryAttempts}/${this.maxRetries})`);
        
        await new Promise(resolve => setTimeout(resolve, this.retryDelay));
        this.retryDelay *= 2; // Exponential backoff
        
        return this.initializeEditor(config);
    }
}

// Global instance
window.RichTextEditorManager = new RichTextEditorManager();

// Utility function for easy initialization
window.initRichTextEditor = function(config) {
    document.addEventListener('DOMContentLoaded', function() {
        window.RichTextEditorManager.initializeEditor(config);
    });
};

// Network status monitoring
window.addEventListener('online', function() {
    console.log('Network connection restored');
});

window.addEventListener('offline', function() {
    console.log('Network connection lost');
});

// Global error handler for unhandled React errors
window.addEventListener('error', function(event) {
    if (event.error && event.error.message && event.error.message.includes('React')) {
        console.error('React error detected, activating fallback mode');
        // Could trigger fallback mode here if needed
    }
});