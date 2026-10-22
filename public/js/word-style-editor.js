/**
 * Microsoft Word Style Rich Text Editor
 * Implements a ribbon interface with tabs and tool groups
 */

const WordStyleEditor = ({ initialContent = '', onChange, onAutoSave }) => {
    const [content, setContent] = React.useState(initialContent);
    const [activeTab, setActiveTab] = React.useState('home');
    const [showColorPicker, setShowColorPicker] = React.useState(false);
    const [showFontColorPicker, setShowFontColorPicker] = React.useState(false);
    const [isUploading, setIsUploading] = React.useState(false);
    const [uploadError, setUploadError] = React.useState('');
    const [validationError, setValidationError] = React.useState('');
    const editorRef = React.useRef(null);
    const selectionRef = React.useRef(null);
    const imageRef = React.useRef(null);
    const dragImageRef = React.useRef(null);
    const [imageAlt, setImageAlt] = React.useState('');
    const [selectedImage, setSelectedImage] = React.useState(false);
    const historyRef = React.useRef({ entries: [initialContent], index: 0 });

    const rememberSelection = () => {
        const selection = window.getSelection();
        if (selection.rangeCount && editorRef.current?.contains(selection.anchorNode)) {
            selectionRef.current = selection.getRangeAt(0).cloneRange();
        }
    };
    React.useEffect(() => {
        document.addEventListener('selectionchange', rememberSelection);
        return () => document.removeEventListener('selectionchange', rememberSelection);
    }, []);
    const restoreSelection = (range = selectionRef.current) => {
        editorRef.current.focus();
        if (range && editorRef.current.contains(range.commonAncestorContainer)) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    };
    const syncContent = (record = true) => {
        const html = editorRef.current.innerHTML;
        setContent(html);
        if (record) {
            const h = historyRef.current;
            if (h.entries[h.index] !== html) {
                h.entries = h.entries.slice(0, h.index + 1).concat(html).slice(-100);
                h.index = h.entries.length - 1;
            }
        }
        if (onChange) onChange(html);
        rememberSelection();
    };
    const updateImage = (action, value) => {
        const image = imageRef.current;
        if (!image || !editorRef.current.contains(image)) return;
        if (action === 'width') { image.style.width = value + '%'; image.style.height = 'auto'; image.removeAttribute('width'); image.removeAttribute('height'); }
        if (action === 'align') {
            image.style.float = value === 'center' ? 'none' : value;
            image.style.display = value === 'center' ? 'block' : 'inline';
            image.style.marginLeft = value === 'left' ? '0' : 'auto';
            image.style.marginRight = value === 'right' ? '0' : 'auto';
        }
        if (action === 'before' || action === 'after') {
            // Move the image across paragraph boundaries, including its containing paragraph.
            let block = image;
            while (block.parentElement !== editorRef.current) block = block.parentElement;
            const target = action === 'before' ? block.previousElementSibling : block.nextElementSibling;
            if (target) {
                if (action === 'before') target.before(image); else target.after(image);
            }
        }
        if (action === 'alt') image.alt = value;
        if (action === 'remove') { image.remove(); imageRef.current = null; setSelectedImage(false); }
        syncContent();
    };

    // Colores predefinidos para el picker
    const colors = [
        '#000000', '#333333', '#666666', '#999999', '#cccccc', '#ffffff',
        '#ff0000', '#ff6600', '#ffcc00', '#00ff00', '#0066ff', '#6600ff',
        '#800000', '#ff3300', '#ff9900', '#99cc00', '#3366ff', '#800080',
        '#660000', '#cc3300', '#ff6600', '#66cc00', '#0033cc', '#663399'
    ];

    // Fuentes disponibles
    const fonts = [
        'Arial', 'Times New Roman', 'Helvetica', 'Georgia', 'Verdana', 
        'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Courier New'
    ];

    // Tamaños de fuente
    const fontSizes = ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28', '32', '36', '48', '72'];

    // Inicialización del editor
    React.useEffect(() => {
        if (editorRef.current) {
            editorRef.current.innerHTML = initialContent;
        }
    }, [initialContent]);

    const undo = () => {
        const h = historyRef.current;
        if (h.index > 0) {
            editorRef.current.innerHTML = h.entries[--h.index];
            imageRef.current = null; setSelectedImage(false); selectionRef.current = null;
            syncContent(false);
        }
    };
    const redo = () => {
        const h = historyRef.current;
        if (h.index < h.entries.length - 1) {
            editorRef.current.innerHTML = h.entries[++h.index];
            imageRef.current = null; setSelectedImage(false); selectionRef.current = null;
            syncContent(false);
        }
    };
    const handleCommand = (command, value = null) => {
        if (command === 'undo') return undo();
        if (command === 'redo') return redo();
        restoreSelection();
        document.execCommand('styleWithCSS', false, true);
        if (command === 'fontSize') {
            // execCommand accepts 1–7, not point sizes. Convert its temporary font nodes.
            document.execCommand('styleWithCSS', false, false);
            document.execCommand('fontSize', false, '7');
            editorRef.current.querySelectorAll('font[size="7"]').forEach(font => {
                const span = document.createElement('span');
                span.style.fontSize = value + 'pt';
                while (font.firstChild) span.append(font.firstChild);
                font.replaceWith(span);
            });
        } else {
            document.execCommand(command, false, value);
        }
        syncContent();
    };

    // Función para subir imágenes
    const handleImageUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        setUploadError('');
        
        // Validar tamaño de archivo (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            setUploadError('El archivo es demasiado grande. Máximo 5MB permitido.');
            return;
        }

        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            setUploadError('Tipo de archivo no válido. Solo se permiten JPG, PNG, GIF y WebP.');
            return;
        }

        const insertionRange = selectionRef.current?.cloneRange();
        setIsUploading(true);
        
        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch('/api/upload-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                restoreSelection(insertionRange);
                const image = document.createElement('img');
                image.src = data.url;
                image.alt = '';
                image.style.width = '100%';
                image.style.height = 'auto';
                const selection = window.getSelection();
                if (selection.rangeCount && editorRef.current.contains(selection.anchorNode)) {
                    const range = selection.getRangeAt(0);
                    range.deleteContents(); range.insertNode(image);
                    range.setStartAfter(image); range.collapse(true);
                    selection.removeAllRanges(); selection.addRange(range);
                } else editorRef.current.append(image);
                imageRef.current = image; setImageAlt(image.alt); setSelectedImage(true);
                syncContent();
                setUploadError('');
            } else {
                throw new Error(data.message || 'Error al subir la imagen');
            }
        } catch (error) {
            console.error('Error al subir la imagen:', error);
            setUploadError(error.message || 'Error desconocido al subir la imagen');
        } finally {
            setIsUploading(false);
            e.target.value = '';
        }
    };

    // Función para insertar enlaces
    const insertLink = () => {
        restoreSelection();
        const selection = window.getSelection();
        const selectedText = selection.toString();
        
        if (selectedText.length === 0) {
            alert('Por favor, seleccione el texto que desea convertir en enlace.');
            return;
        }

        const url = prompt('Ingrese la URL del enlace:', 'https://');
        
        if (url && url.trim() !== '' && url !== 'https://') {
            try {
                new URL(url);
                handleCommand('createLink', url);
                
                // Agregar estilos para enlaces
                setTimeout(() => {
                    const links = editorRef.current.querySelectorAll('a[href="' + url + '"]');
                    links.forEach(link => {
                        link.style.color = '#007bff';
                        link.style.textDecoration = 'underline';
                        
                        if (url.startsWith('http') && !url.includes(window.location.hostname)) {
                            link.setAttribute('target', '_blank');
                            link.setAttribute('rel', 'noopener noreferrer');
                        }
                    });
                    syncContent();
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
            
            const linkElement = parentElement.closest('a');
            if (linkElement) {
                const textNode = document.createTextNode(linkElement.textContent);
                linkElement.parentNode.replaceChild(textNode, linkElement);
                
                const newContent = editorRef.current.innerHTML;
                syncContent();
            } else {
                alert('No hay ningún enlace seleccionado para quitar.');
            }
        } else {
            alert('Por favor, seleccione el enlace que desea quitar.');
        }
    };

    // Manejar cambios en el contenido
    const handleChange = () => syncContent();

    // Manejar atajos de teclado
    const handleKeyDown = (e) => {
        // No interferir con la escritura de simbolos (Alt+numeros, teclado de terceros, IME)
        if (e.isComposing || e.keyCode === 229) return;

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
                    // The manager submits this editor's own form, with validation.
                    break;
            }
        }

        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleCommand('insertParagraph');
        }
    };

    // Crear botón de herramienta
    const createToolButton = (command, icon, title, value = null) => {
        return React.createElement(
            'button',
            {
                className: 'word-button',
                onMouseDown: e => e.preventDefault(),
                onClick: () => value ? handleCommand(command, value) : handleCommand(command),
                title: title,
                type: 'button',
                key: command + (value || '')
            },
            icon
        );
    };

    // Crear separador
    const createSeparator = (key) => {
        return React.createElement('div', {
            className: 'separator',
            key: key
        });
    };

    return React.createElement(
        'div',
        { 
            className: 'word-editor-container',
            style: { direction: 'ltr' }
        },
        [
            // Mensajes de error
            (uploadError || validationError) && React.createElement(
                'div',
                {
                    key: 'error-messages',
                    className: 'alert alert-danger',
                    role: 'alert'
                },
                uploadError || validationError
            ),
            
            // Barra de herramientas estilo Microsoft Word
            React.createElement(
                'div',
                { 
                    key: 'word-toolbar',
                    className: 'word-toolbar',
                    role: 'toolbar'
                },
                [
                    // Pestañas del ribbon
                    React.createElement(
                        'div',
                        { className: 'ribbon-tabs', key: 'ribbon-tabs' },
                        [
                            React.createElement(
                                'button',
                                {
                                    className: `ribbon-tab ${activeTab === 'home' ? 'active' : ''}`,
                                    type: 'button',
                                    onClick: () => setActiveTab('home'),
                                    key: 'home-tab'
                                },
                                'Inicio'
                            ),
                            React.createElement(
                                'button',
                                {
                                    className: `ribbon-tab ${activeTab === 'insert' ? 'active' : ''}`,
                                    type: 'button',
                                    onClick: () => setActiveTab('insert'),
                                    key: 'insert-tab'
                                },
                                'Insertar'
                            )
                        ]
                    ),

                    // Contenido del ribbon
                    React.createElement(
                        'div',
                        { className: 'ribbon-content', key: 'ribbon-content' },
                        activeTab === 'home' ? [
                            // Grupo Portapapeles
                            React.createElement('div', { className: 'toolbar-group', key: 'clipboard' }, [
                                React.createElement('div', { className: 'group-label', key: 'clipboard-label' }, 'Portapapeles'),
                                React.createElement('div', { className: 'group-buttons', key: 'clipboard-buttons' }, [
                                    createToolButton('undo', '↶', 'Deshacer'),
                                    createToolButton('redo', '↷', 'Rehacer'),
                                    createToolButton('copy', '📋', 'Copiar'),
                                    createToolButton('cut', '✂️', 'Cortar'),
                                    createToolButton('paste', '📄', 'Pegar')
                                ])
                            ]),

                            createSeparator('sep1'),

                            // Grupo Fuente
                            React.createElement('div', { className: 'toolbar-group', key: 'font' }, [
                                React.createElement('div', { className: 'group-label', key: 'font-label' }, 'Fuente'),
                                React.createElement('div', { className: 'group-buttons', key: 'font-buttons' }, [
                                    React.createElement('select', {
                                        className: 'font-family-select',
                                        onChange: (e) => handleCommand('fontName', e.target.value),
                                        title: 'Fuente',
                                        key: 'font-family',
                                        defaultValue: 'Arial'
                                    }, fonts.map(font => 
                                        React.createElement('option', { value: font, key: font }, font)
                                    )),
                                    React.createElement('select', {
                                        className: 'font-size-select',
                                        onChange: (e) => handleCommand('fontSize', e.target.value),
                                        title: 'Tamaño',
                                        key: 'font-size',
                                        defaultValue: '12'
                                    }, fontSizes.map(size => 
                                        React.createElement('option', { value: size, key: size }, size)
                                    )),
                                    createToolButton('bold', 'B', 'Negrita'),
                                    createToolButton('italic', 'I', 'Cursiva'),
                                    createToolButton('underline', 'U', 'Subrayado'),
                                    createToolButton('strikethrough', 'S', 'Tachado'),
                                    createToolButton('subscript', 'X₂', 'Subíndice'),
                                    createToolButton('superscript', 'X²', 'Superíndice')
                                ])
                            ]),

                            createSeparator('sep2'),

                            // Grupo Color
                            React.createElement('div', { className: 'toolbar-group color-picker-container', key: 'colors' }, [
                                React.createElement('div', { className: 'group-label', key: 'color-label' }, 'Color'),
                                React.createElement('div', { className: 'group-buttons', key: 'color-buttons' }, [
                                    React.createElement('button', {
                                        className: 'word-button color-button',
                                        onClick: () => setShowColorPicker(!showColorPicker),
                                        title: 'Color de texto',
                                        key: 'text-color'
                                    }, [
                                        'A',
                                        React.createElement('div', {
                                            className: 'color-bar',
                                            key: 'color-bar'
                                        })
                                    ]),
                                    showColorPicker && React.createElement('div', {
                                        className: 'color-grid',
                                        key: 'color-grid'
                                    }, colors.map(color => 
                                        React.createElement('button', {
                                            className: 'color-swatch',
                                            style: { backgroundColor: color },
                                            onClick: () => {
                                                handleCommand('foreColor', color);
                                                setShowColorPicker(false);
                                            },
                                            key: color
                                        })
                                    ))
                                ])
                            ]),

                            createSeparator('sep3'),

                            // Grupo Párrafo
                            React.createElement('div', { className: 'toolbar-group', key: 'paragraph' }, [
                                React.createElement('div', { className: 'group-label', key: 'paragraph-label' }, 'Párrafo'),
                                React.createElement('div', { className: 'group-buttons', key: 'paragraph-buttons' }, [
                                    createToolButton('insertUnorderedList', '•', 'Lista con viñetas'),
                                    createToolButton('insertOrderedList', '1.', 'Lista numerada'),
                                    createToolButton('outdent', '⬅', 'Disminuir sangría'),
                                    createToolButton('indent', '➡', 'Aumentar sangría'),
                                    createToolButton('justifyLeft', '⬅', 'Alinear izquierda'),
                                    createToolButton('justifyCenter', '⬌', 'Centrar'),
                                    createToolButton('justifyRight', '➡', 'Alinear derecha'),
                                    createToolButton('justifyFull', '⬍', 'Justificar')
                                ])
                            ]),

                            createSeparator('sep4'),

                            // Grupo Enlaces
                            React.createElement('div', { className: 'toolbar-group', key: 'links' }, [
                                React.createElement('div', { className: 'group-label', key: 'links-label' }, 'Enlaces'),
                                React.createElement('div', { className: 'group-buttons', key: 'links-buttons' }, [
                                    React.createElement('button', {
                                        className: 'word-button',
                                        onClick: insertLink,
                                        title: 'Insertar enlace',
                                        key: 'insert-link',
                                        type: 'button'
                                    }, '🔗'),
                                    React.createElement('button', {
                                        className: 'word-button',
                                        onClick: removeLink,
                                        title: 'Quitar enlace',
                                        key: 'remove-link',
                                        type: 'button'
                                    }, '🚫')
                                ])
                            ])
                        ] : [
                            // Pestaña Insertar
                            React.createElement('div', { className: 'toolbar-group', key: 'insert-images' }, [
                                React.createElement('div', { className: 'group-label', key: 'images-label' }, 'Imágenes'),
                                React.createElement('div', { className: 'group-buttons', key: 'images-buttons' }, [
                                    React.createElement('label', {
                                        className: `word-button ${isUploading ? 'uploading' : ''}`,
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
                                ])
                            ])
                        ]
                    )
                ]
            ),
            
            React.createElement('p', {key: 'save-help', className: 'editor-save-help'},
                'Para guardar la noticia en el servidor, pulsa Guardar o Actualizar noticia al final del formulario.'),
            selectedImage && React.createElement('div', {key: 'image-tools', className: 'editor-image-tools', role: 'group', 'aria-label': 'Ajustes de imagen'}, [
                React.createElement('strong', {key:'label'}, 'Imagen seleccionada: '),
                ...[25, 50, 75, 100].map(width => React.createElement('button', {type:'button', key:'w'+width, onMouseDown:e=>e.preventDefault(), onClick:()=>updateImage('width',width)}, width+'%')),
                ...[['left','Izquierda'],['center','Centrada'],['right','Derecha'],['before','Mover antes'],['after','Mover después'],['remove','Quitar imagen']].map(([action,label]) => React.createElement('button', {type:'button', key:action, onMouseDown:e=>e.preventDefault(), onClick:()=>updateImage(['left','center','right'].includes(action)?'align':action,action)}, label)),
                React.createElement('label', {key:'alt'}, ['Descripción de imagen ', React.createElement('input', {key:'input', type:'text', value:imageAlt, onChange:e=>{setImageAlt(e.target.value);updateImage('alt',e.target.value);}})])
            ]),
            // Área editable
            React.createElement(
                'div',
                {
                    key: 'editor',
                    ref: editorRef,
                    className: 'word-editor-content',
                    contentEditable: true,
                    onInput: handleChange,
                    onClick: e => {
                        imageRef.current = e.target.tagName === 'IMG' ? e.target : null;
                        setSelectedImage(!!imageRef.current);
                        setImageAlt(imageRef.current?.alt || '');
                    },
                    onDragStart: e => {
                        dragImageRef.current = e.target.tagName === 'IMG' ? e.target : null;
                    },
                    onDragEnd: () => { dragImageRef.current = null; },
                    onDrop: e => {
                        const image = dragImageRef.current;
                        if (!image || !editorRef.current.contains(image)) return;
                        const range = document.caretRangeFromPoint?.(e.clientX, e.clientY);
                        if (!range || !editorRef.current.contains(range.startContainer) || image.contains(range.startContainer)) return;
                        e.preventDefault();
                        range.insertNode(image);
                        imageRef.current = image; dragImageRef.current = null;
                        setImageAlt(image.alt); setSelectedImage(true); syncContent();
                    },
                    onKeyDown: handleKeyDown,
                    role: 'textbox',
                    'aria-label': 'Editor de texto enriquecido estilo Microsoft Word',
                    'aria-multiline': 'true'
                }
            )
        ]
    );
};

// Exportar el componente
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WordStyleEditor;
} else if (typeof window !== 'undefined') {
    window.WordStyleEditor = WordStyleEditor;
}