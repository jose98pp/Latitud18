{{-- Rich Text Editor Quick Help Card --}}
<div class="card border-info mb-3" id="editor-help-card" style="display: none;">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Ayuda Rápida - Editor de Texto
        </h6>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleEditorHelp()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6 class="text-primary">
                    <i class="fas fa-keyboard me-1"></i>
                    Atajos Rápidos
                </h6>
                <ul class="list-unstyled small">
                    <li><kbd>Ctrl+B</kbd> Negrita</li>
                    <li><kbd>Ctrl+I</kbd> Cursiva</li>
                    <li><kbd>Ctrl+U</kbd> Subrayado</li>
                    <li><kbd>Ctrl+S</kbd> Guardar</li>
                    <li><kbd>Ctrl+Z</kbd> Deshacer</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-success">
                    <i class="fas fa-link me-1"></i>
                    Enlaces y Referencias
                </h6>
                <ul class="list-unstyled small">
                    <li>🔗 Selecciona texto + botón enlace</li>
                    <li>🌐 URLs completas (https://...)</li>
                    <li>🔗❌ Quitar enlaces fácilmente</li>
                    <li>🆕 Enlaces externos en nueva ventana</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-info">
                    <i class="fas fa-image me-1"></i>
                    Imágenes
                </h6>
                <ul class="list-unstyled small">
                    <li>📷 Formatos: JPG, PNG, GIF, WebP</li>
                    <li>📏 Máximo: 5 MB</li>
                    <li>⚡ Subida automática</li>
                    <li>🔄 Reintentos automáticos</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Consejos
                </h6>
                <ul class="list-unstyled small">
                    <li>💾 Guardado automático cada 3s</li>
                    <li>👁️ Use vista previa antes de guardar</li>
                    <li>📝 Mínimo 10 caracteres</li>
                    <li>🚫 Evite copiar desde Word</li>
                </ul>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showEditorKeyboardShortcuts()">
                        <i class="fas fa-keyboard me-1"></i>
                        Ver Todos los Atajos
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="showEditorFullHelp()">
                        <i class="fas fa-question-circle me-1"></i>
                        Ayuda Completa
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditorHelp()">
                        <i class="fas fa-eye-slash me-1"></i>
                        Ocultar Ayuda
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle help card visibility
    function toggleEditorHelp() {
        const helpCard = document.getElementById('editor-help-card');
        const isVisible = helpCard.style.display !== 'none';
        
        helpCard.style.display = isVisible ? 'none' : 'block';
        
        // Save preference in localStorage
        localStorage.setItem('editorHelpVisible', !isVisible);
    }

    // Show keyboard shortcuts modal
    function showEditorKeyboardShortcuts() {
        if (window.RichTextEditorManager) {
            window.RichTextEditorManager.showKeyboardShortcutsModal();
        }
    }

    // Show full help modal
    function showEditorFullHelp() {
        if (window.RichTextEditorManager) {
            window.RichTextEditorManager.showHelpModal();
        }
    }

    // Auto-show help card for new users
    document.addEventListener('DOMContentLoaded', function() {
        const helpVisible = localStorage.getItem('editorHelpVisible');
        const isFirstTime = localStorage.getItem('editorFirstTime') === null;
        
        if (isFirstTime || helpVisible === 'true') {
            document.getElementById('editor-help-card').style.display = 'block';
            
            if (isFirstTime) {
                localStorage.setItem('editorFirstTime', 'false');
                localStorage.setItem('editorHelpVisible', 'true');
            }
        }
    });
</script>

<style>
    #editor-help-card {
        position: sticky;
        top: 20px;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    #editor-help-card kbd {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        padding: 2px 4px;
        font-size: 0.75em;
    }

    #editor-help-card .card-body {
        padding: 1rem;
    }

    #editor-help-card .list-unstyled li {
        margin-bottom: 0.25rem;
    }

    @media (max-width: 768px) {
        #editor-help-card {
            position: relative;
            top: auto;
        }
        
        #editor-help-card .d-flex {
            flex-direction: column;
        }
        
        #editor-help-card .btn {
            margin-bottom: 0.5rem;
        }
    }
</style>