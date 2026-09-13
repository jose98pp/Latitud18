const RichTextEditor = ({ initialContent = '', onChange, onAutoSave }) => {
    const [content, setContent] = React.useState(initialContent);
    const [previewMode, setPreviewMode] = React.useState(false);
    const [showColorPicker, setShowColorPicker] = React.useState(false);
    const [undoStack, setUndoStack] = React.useState([initialContent]);
    const [undoIndex, setUndoIndex] = React.useState(0);
    const [isUploading, setIsUploading] = React.useState(false);
    const [uploadError, setUploadError] = React.useState('');
    const [validationError, setValidationError] = React.useState('');
    const [networkError, setNetworkError] = React.useState('');
    const [retryCount, setRetryCount] = React.useState(0);
    const [editorFailed, setEditorFailed] = React.useState(false);
    const editorRef = React.useRef(null);
    const autoSaveTimerRef = React.useRef(null);
    const maxRetries = 3;

    // Colores predefinidos
    const colors = [
        '#000000', '#333333', '#666666', '#999999', 
        '#ff0000', '#00ff00', '#0000ff', '#ffff00',
        '#ff00ff', '#00ffff', '#800000', '#008000',
        '#000080', '#808000', '#800080', '#008080'
    ];

    // Tamaños de fuente predefinidos
    const fontSizes = [
        { name: 'Pequeño', value: '12px' },
        { name: 'Normal', value: '16px' },
        { name: 'Mediano', value: '20px' },
        { name: 'Grande', value: '24px' },
        { name: 'Muy Grande', value: '32px' }
    ];

    React.useEffect(() => {
        try {
            if (editorRef.current) {
                editorRef.current.innerHTML = initialContent;
            }
        } catch (error) {
            console.error('Error initializing editor:', error);
            setEditorFailed(true);
        }
    }, [initialContent]);

    // Error boundary effect
    React.useEffect(() => {
        const handleError = (event) => {
            if (event.error && event.error.message && event.error.message.includes('React')) {
                console.error('React error detected:', event.error);
                setEditorFailed(true);
            }
        };

        window.addEventListener('error', handleError);
        return () => window.removeEventListener('error', handleError);
    }, []);

    // Función para subir imágenes con mejor manejo de errores, reintentos y indicadores de progreso
    const handleImageUpload = async (e, attempt = 1) => {
        const file = e.target.files[0];
        if (!file) return;

        // Clear previous errors
        setUploadError('');
        setNetworkError('');
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            setUploadError('El archivo es demasiado grande. Máximo 5MB permitido.');
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            setUploadError('Tipo de archivo no válido. Solo se permiten JPG, PNG, GIF y WebP.');
            return;
        }

        setIsUploading(true);
        
        // Show upload progress notification
        const uploadNotification = document.createElement('div');
        uploadNotification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        uploadNotification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        uploadNotification.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <div>
                    <strong>Subiendo imagen...</strong><br>
                    <small class="text-muted">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                </div>
            </div>
        `;
        document.body.appendChild(uploadNotification);
        const formData = new FormData();
        formData.append('image', file);

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

            const response = await fetch('/api/upload-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                handleCommand('insertImage', data.url);
                setUploadError('');
                setNetworkError('');
                setRetryCount(0);
                
                // Show success notification
                uploadNotification.className = 'alert alert-success alert-dismissible fade show position-fixed';
                uploadNotification.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <strong>¡Imagen subida exitosamente!</strong><br>
                            <small class="text-muted">La imagen se ha insertado en el contenido</small>
                        </div>
                    </div>
                `;
                
                // Auto-hide success notification
                setTimeout(() => {
                    if (uploadNotification.parentNode) {
                        uploadNotification.parentNode.removeChild(uploadNotification);
                    }
                }, 3000);
            } else {
                throw new Error(data.message || 'Error al subir la imagen');
            }
        } catch (error) {
            console.error('Error al subir la imagen (intento ' + attempt + '):', error);
            
            let errorMessage = '';
            
            if (error.name === 'AbortError') {
                errorMessage = 'Tiempo de espera agotado. La conexión es muy lenta.';
            } else if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
                errorMessage = 'Error de conexión. Verifique su conexión a internet.';
            } else if (error.message.includes('HTTP 413')) {
                errorMessage = 'El archivo es demasiado grande para el servidor.';
            } else if (error.message.includes('HTTP 507')) {
                errorMessage = 'Espacio insuficiente en el servidor.';
            } else if (error.message.includes('HTTP 422')) {
                errorMessage = 'Archivo no válido. Verifique el formato y tamaño.';
            } else {
                errorMessage = error.message || 'Error desconocido al subir la imagen';
            }

            // Retry logic for network errors
            if ((error.name === 'AbortError' || error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) && attempt < maxRetries) {
                setNetworkError(`Error de conexión. Reintentando... (${attempt}/${maxRetries})`);
                setRetryCount(attempt);
                
                // Exponential backoff: wait 2^attempt seconds
                setTimeout(() => {
                    handleImageUpload(e, attempt + 1);
                }, Math.pow(2, attempt) * 1000);
                
                return;
            }

            setUploadError(errorMessage);
            setRetryCount(0);
            
            // Show error notification
            if (uploadNotification.parentNode) {
                uploadNotification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                uploadNotification.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Error al subir imagen</strong><br>
                            <small class="text-muted">${errorMessage}</small>
                        </div>
                    </div>
                `;
                
                // Auto-hide error notification after longer delay
                setTimeout(() => {
                    if (uploadNotification.parentNode) {
                        uploadNotification.parentNode.removeChild(uploadNotification);
                    }
                }, 5000);
            }
        } finally {
            if (attempt >= maxRetries || !networkError) {
                setIsUploading(false);
                // Clear the file input
                e.target.value = '';
            }
        }
    };

    // Función para el guardado automático
    React.useEffect(() => {
        if (autoSaveTimerRef.current) {
            clearTimeout(autoSaveTimerRef.current);
        }

        autoSaveTimerRef.current = setTimeout(() => {
            if (onAutoSave) {
                onAutoSave(content);
            }
        }, 3000);

        return () => {
            if (autoSaveTimerRef.current) {
                clearTimeout(autoSaveTimerRef.current);
            }
        };
    }, [content, onAutoSave]);

    // Funciones de deshacer/rehacer
    const addToUndoStack = (newContent) => {
        const newStack = undoStack.slice(0, undoIndex + 1);
        newStack.push(newContent);
        setUndoStack(newStack);
        setUndoIndex(newStack.length - 1);
    };

    const undo = () => {
        if (undoIndex > 0) {
            setUndoIndex(undoIndex - 1);
            editorRef.current.innerHTML = undoStack[undoIndex - 1];
            setContent(undoStack[undoIndex - 1]);
        }
    };

    const redo = () => {
        if (undoIndex < undoStack.length - 1) {
            setUndoIndex(undoIndex + 1);
            editorRef.current.innerHTML = undoStack[undoIndex + 1];
            setContent(undoStack[undoIndex + 1]);
        }
    };

    const handleCommand = (command, value = null) => {
        document.execCommand(command, false, value);
        editorRef.current?.focus();
        const newContent = editorRef.current.innerHTML;
        setContent(newContent);
        addToUndoStack(newContent);
    };

    // Función para insertar enlaces
    const insertLink = () => {
        const selection = window.getSelection();
        const selectedText = selection.toString();
        
        if (selectedText.length === 0) {
            alert('Por favor, seleccione el texto que desea convertir en enlace.');
            return;
        }

        const url = prompt('Ingrese la URL del enlace:', 'https://');
        
        if (url && url.trim() !== '' && url !== 'https://') {
            // Validar URL básica
            try {
                new URL(url);
                handleCommand('createLink', url);
                
                // Agregar target="_blank", rel="noopener" y estilos para enlaces
                setTimeout(() => {
                    const links = editorRef.current.querySelectorAll('a[href="' + url + '"]');
                    links.forEach(link => {
                        // Estilos base para todos los enlaces
                        link.style.color = '#007bff';
                        link.style.textDecoration = 'underline';
                        link.style.fontWeight = '500';
                        link.style.transition = 'all 0.3s ease';
                        
                        if (url.startsWith('http') && !url.includes(window.location.hostname)) {
                            // Enlaces externos
                            link.setAttribute('target', '_blank');
                            link.setAttribute('rel', 'noopener noreferrer');
                            link.setAttribute('title', 'Enlace externo - Se abre en nueva ventana');
                            link.style.color = '#28a745';
                            link.classList.add('external-link');
                            
                            // Agregar icono de enlace externo
                            if (!link.querySelector('.external-icon')) {
                                const icon = document.createElement('span');
                                icon.className = 'external-icon';
                                icon.innerHTML = ' 🔗';
                                icon.style.fontSize = '0.8em';
                                icon.style.opacity = '0.7';
                                link.appendChild(icon);
                            }
                        } else {
                            // Enlaces internos
                            link.style.color = '#6f42c1';
                            link.classList.add('internal-link');
                        }
                        
                        // Efectos hover
                        link.addEventListener('mouseenter', function() {
                            this.style.backgroundColor = 'rgba(0, 123, 255, 0.1)';
                            this.style.borderRadius = '3px';
                            this.style.padding = '2px 4px';
                        });
                        
                        link.addEventListener('mouseleave', function() {
                            this.style.backgroundColor = 'transparent';
                            this.style.padding = '0';
                        });
                    });
                }, 100);
            } catch (e) {
                alert('Por favor, ingrese una URL válida (ejemplo: https://www.ejemplo.com)');
            }
        }
    };

    // Función para quitar enlaces
    const removeLink = () => {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const parentElement = range.commonAncestorContainer.nodeType === Node.TEXT_NODE 
                ? range.commonAncestorContainer.parentElement 
                : range.commonAncestorContainer;
            
            // Buscar el enlace más cercano
            const linkElement = parentElement.closest('a');
            if (linkElement) {
                // Reemplazar el enlace con solo su texto
                const textNode = document.createTextNode(linkElement.textContent);
                linkElement.parentNode.replaceChild(textNode, linkElement);
                
                const newContent = editorRef.current.innerHTML;
                setContent(newContent);
                addToUndoStack(newContent);
            } else {
                alert('No hay ningún enlace seleccionado para quitar.');
            }
        } else {
            alert('Por favor, seleccione el enlace que desea quitar.');
        }
    };

    const handleKeyDown = (e) => {
        // Atajos de teclado
        if (e.ctrlKey || e.metaKey) {
            switch (e.key.toLowerCase()) {
                case 'z':
                    e.preventDefault();
                    if (e.shiftKey) {
                        redo();
                    } else {
                        undo();
                    }
                    break;
                case 'y':
                    e.preventDefault();
                    redo();
                    break;
                case 's':
                    e.preventDefault();
                    if (onAutoSave) {
                        onAutoSave(content);
                    }
                    break;
            }
        }

        // Manejar Enter
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleCommand('insertParagraph');
        }
    };

    // Content validation and sanitization
    const validateContent = (content) => {
        try {
            // Remove potentially dangerous scripts and event handlers
            let sanitized = content.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            sanitized = sanitized.replace(/on\w+\s*=\s*["'][^"']*["']/gi, '');
            sanitized = sanitized.replace(/javascript\s*:/gi, '');
            
            // Check content length (max 250000 characters)
            const textLength = sanitized.replace(/<[^>]*>/g, '').length;
            if (textLength > 250000) {
                setValidationError('El contenido es demasiado largo. Máximo 250,000 caracteres.');
                return false;
            }
            
            // Check minimum content length
            if (textLength < 1) {
                setValidationError('');
                return sanitized;
            }
            
            setValidationError('');
            return sanitized;
        } catch (error) {
            console.error('Error validating content:', error);
            setValidationError('Error al validar el contenido.');
            return false;
        }
    };

    // Fallback mechanism when Rich Text Editor fails
    const renderFallbackEditor = () => {
        return React.createElement(
            'div',
            { className: 'w-full border rounded-lg shadow-sm bg-white' },
            [
                React.createElement(
                    'div',
                    {
                        key: 'fallback-notice',
                        className: 'p-3 bg-yellow-50 border-b border-yellow-200 text-yellow-800 text-sm',
                        role: 'alert'
                    },
                    'El editor avanzado no está disponible. Usando editor básico.'
                ),
                React.createElement('textarea', {
                    key: 'fallback-textarea',
                    className: 'w-full p-4 min-h-[300px] border-0 resize-vertical focus:outline-none focus:ring-2 focus:ring-blue-500',
                    value: content,
                    onChange: (e) => {
                        const newContent = e.target.value;
                        setContent(newContent);
                        if (onChange) {
                            onChange(newContent);
                        }
                    },
                    placeholder: 'Escriba su contenido aquí...',
                    'aria-label': 'Editor de texto básico'
                })
            ]
        );
    };

    const handleChange = (e) => {
        const newContent = e.currentTarget.innerHTML;
        const validatedContent = validateContent(newContent);
        
        if (validatedContent !== false) {
            setContent(validatedContent);
            if (onChange) {
                onChange(validatedContent);
            }
        }
    };

    const createButton = (command, icon, title, ariaLabel) => {
        return React.createElement(
            'button',
            {
                className: 'p-2 hover:bg-gray-200 rounded transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500',
                onClick: () => handleCommand(command),
                title: title,
                'aria-label': ariaLabel || title,
                key: command,
                type: 'button',
                tabIndex: 0
            },
            icon
        );
    };

    const createSeparator = (key) => {
        return React.createElement('div', {
            className: 'w-px h-6 bg-gray-300 my-auto mx-2',
            key: key
        });
    };

    // Renderizado condicional para modo vista previa
    if (previewMode) {
        return React.createElement(
            'div',
            {
                className: 'border rounded-lg p-4 prose max-w-none',
                dangerouslySetInnerHTML: { __html: content }
            }
        );
    }

    // Fallback to basic textarea if editor failed
    if (editorFailed) {
        return renderFallbackEditor();
    }

    return React.createElement(
        'div',
        { 
            className: 'w-full border rounded-lg shadow-sm bg-white',
            style: { direction: 'ltr' }
        },
        [
            // Error messages
            (uploadError || validationError || networkError) && React.createElement(
                'div',
                {
                    key: 'error-messages',
                    className: 'p-3 bg-red-50 border-b border-red-200 text-red-700 text-sm',
                    role: 'alert',
                    'aria-live': 'polite'
                },
                [
                    uploadError && React.createElement('div', { key: 'upload-error' }, uploadError),
                    validationError && React.createElement('div', { key: 'validation-error' }, validationError),
                    networkError && React.createElement('div', { 
                        key: 'network-error',
                        className: 'flex items-center gap-2'
                    }, [
                        React.createElement('span', { key: 'spinner' }, '⏳'),
                        React.createElement('span', { key: 'message' }, networkError)
                    ])
                ]
            ),
            
            // Barra de herramientas estilo Microsoft Word
            React.createElement(
                'div',
                { 
                    key: 'toolbar',
                    className: 'word-toolbar border-b bg-white sticky top-0 overflow-x-auto shadow-sm',
                    role: 'toolbar',
                    'aria-label': 'Herramientas de formato de texto'
                },
                [
                    // Grupo 1: Portapapeles
                    React.createElement('div', { className: 'toolbar-group', key: 'clipboard' }, [
                        createButton('undo', '↶', 'Deshacer (Ctrl+Z)', 'Deshacer último cambio'),
                        createButton('redo', '↷', 'Rehacer (Ctrl+Y)', 'Rehacer último cambio'),
                    ]),

                    // Grupo 2: Fuente
                    React.createElement('div', { className: 'toolbar-group', key: 'font' }, [
                        React.createElement('select', {
                            className: 'font-family-select',
                            onChange: (e) => handleCommand('fontName', e.target.value),
                            title: 'Fuente',
                            key: 'font-family',
                            defaultValue: 'Arial'
                        }, [
                            React.createElement('option', { value: 'Arial', key: 'arial' }, 'Arial'),
                            React.createElement('option', { value: 'Times New Roman', key: 'times' }, 'Times New Roman'),
                            React.createElement('option', { value: 'Helvetica', key: 'helvetica' }, 'Helvetica'),
                            React.createElement('option', { value: 'Georgia', key: 'georgia' }, 'Georgia'),
                            React.createElement('option', { value: 'Verdana', key: 'verdana' }, 'Verdana')
                        ]),
                        React.createElement('select', {
                            onChange: (e) => handleCommand('fontSize', e.target.value),
                            title: 'Tamaño de fuente',
                            key: 'fontsize',
                            defaultValue: '3'
                        }, [
                            React.createElement('option', { value: '1', key: '8' }, '8'),
                            React.createElement('option', { value: '2', key: '10' }, '10'),
                            React.createElement('option', { value: '3', key: '12' }, '12'),
                            React.createElement('option', { value: '4', key: '14' }, '14'),
                            React.createElement('option', { value: '5', key: '18' }, '18'),
                            React.createElement('option', { value: '6', key: '24' }, '24'),
                            React.createElement('option', { value: '7', key: '36' }, '36')
                        ])
                    ]),

                    // Grupo 3: Formato básico
                    React.createElement('div', { className: 'toolbar-group', key: 'basic-format' }, [
                        createButton('bold', 'B', 'Negrita (Ctrl+B)', 'Aplicar formato negrita'),
                        createButton('italic', 'I', 'Cursiva (Ctrl+I)', 'Aplicar formato cursiva'),
                        createButton('underline', 'U', 'Subrayado (Ctrl+U)', 'Aplicar subrayado'),
                        createButton('strikethrough', 'S', 'Tachado', 'Aplicar tachado'),
                    ]),

                    // Grupo 4: Color
                    React.createElement('div', { className: 'toolbar-group color-picker-container', key: 'colors' }, [
                        React.createElement('button', {
                            onClick: () => setShowColorPicker(!showColorPicker),
                            title: 'Color de texto',
                            key: 'text-color',
                            style: { position: 'relative' }
                        }, [
                            'A',
                            React.createElement('div', {
                                style: {
                                    position: 'absolute',
                                    bottom: '2px',
                                    left: '50%',
                                    transform: 'translateX(-50%)',
                                    width: '16px',
                                    height: '3px',
                                    backgroundColor: '#000000'
                                },
                                key: 'color-bar'
                            })
                        ]),
                        showColorPicker && React.createElement('div', {
                            className: 'color-grid',
                            key: 'color-grid'
                        }, colors.map(color => 
                            React.createElement('button', {
                                style: { backgroundColor: color },
                                onClick: () => {
                                    handleCommand('foreColor', color);
                                    setShowColorPicker(false);
                                },
                                key: color
                            })
                        ))
                    ]),

                    // Grupo 5: Párrafo
                    React.createElement('div', { className: 'toolbar-group', key: 'paragraph' }, [
                        createButton('justifyLeft', '⬅', 'Alinear a la izquierda'),
                        createButton('justifyCenter', '⬌', 'Centrar'),
                        createButton('justifyRight', '➡', 'Alinear a la derecha'),
                        createButton('justifyFull', '⬍', 'Justificar'),
                    ]),

                    // Grupo 6: Listas
                    React.createElement('div', { className: 'toolbar-group', key: 'lists' }, [
                        createButton('insertUnorderedList', '•', 'Lista con viñetas'),
                        createButton('insertOrderedList', '1.', 'Lista numerada'),
                        createButton('outdent', '⬅', 'Disminuir sangría'),
                        createButton('indent', '➡', 'Aumentar sangría'),
                    ]),

                    // Grupo 7: Insertar
                    React.createElement('div', { className: 'toolbar-group', key: 'insert' }, [
                        React.createElement('button', {
                            onClick: () => insertLink(),
                            title: 'Insertar enlace',
                            key: 'insert-link',
                            type: 'button'
                        }, '🔗'),
                        React.createElement('button', {
                            onClick: () => removeLink(),
                            title: 'Quitar enlace',
                            key: 'remove-link',
                            type: 'button'
                        }, '🚫'),
                        React.createElement('label', {
                            className: `${isUploading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`,
                            title: isUploading ? 'Subiendo imagen...' : 'Insertar imagen',
                            key: 'image-upload'
                        }, [
                            isUploading ? '⏳' : '🖼',
                            React.createElement('input', {
                                type: 'file',
                                style: { display: 'none' },
                                accept: 'image/jpeg,image/jpg,image/png,image/gif,image/webp',
                                onChange: handleImageUpload,
                                disabled: isUploading,
                                key: 'image-input'
                            })
                        ])
                    ]),

                    // Grupo 8: Vista
                    React.createElement('div', { className: 'toolbar-group', key: 'view' }, [
                        React.createElement('button', {
                            onClick: () => setPreviewMode(!previewMode),
                            title: 'Vista previa',
                            key: 'preview'
                        }, '👁')
                    ])
                ]
            ),
            // Área editable
            React.createElement(
                'div',
                {
                    key: 'editor',
                    ref: editorRef,
                    className: 'p-2 sm:p-4 min-h-[200px] sm:min-h-[300px] focus:outline-none focus:ring-2 focus:ring-blue-500 prose max-w-none',
                    contentEditable: true,
                    onInput: handleChange,
                    onKeyDown: handleKeyDown,
                    role: 'textbox',
                    'aria-label': 'Editor de texto enriquecido',
                    'aria-multiline': 'true',
                    'aria-describedby': uploadError || validationError ? 'error-messages' : undefined,
                    style: {
                        direction: 'ltr',
                        unicodeBidi: 'bidi-override'
                    }
                }
            )
        ]
    );
};

// Exportar el componente
typeof module !== 'undefined' && (module.exports = RichTextEditor);