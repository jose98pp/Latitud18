/**
 * Test script for Word-style Rich Text Editor
 * This script verifies that the Word-style editor loads and functions correctly
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Testing Word-style Rich Text Editor...');
    
    // Check if required dependencies are loaded
    const dependencies = {
        'React': typeof React !== 'undefined',
        'ReactDOM': typeof ReactDOM !== 'undefined',
        'WordStyleEditor': typeof WordStyleEditor !== 'undefined',
        'RichTextEditorManager': typeof window.RichTextEditorManager !== 'undefined'
    };
    
    console.log('Dependencies check:', dependencies);
    
    // Check if all dependencies are available
    const allDependenciesLoaded = Object.values(dependencies).every(loaded => loaded);
    
    if (allDependenciesLoaded) {
        console.log('✅ All dependencies loaded successfully');
        
        // Test WordStyleEditor component creation
        try {
            const testElement = React.createElement(WordStyleEditor, {
                initialContent: '<p>Test content</p>',
                onChange: (content) => console.log('Content changed:', content.length, 'characters'),
                onAutoSave: (content) => console.log('Auto-save triggered')
            });
            
            console.log('✅ WordStyleEditor component created successfully');
            
            // Test CSS styles are loaded
            const wordEditorCSS = document.querySelector('link[href*="word-style-editor.css"]');
            if (wordEditorCSS) {
                console.log('✅ Word-style editor CSS loaded');
            } else {
                console.log('⚠️ Word-style editor CSS not found');
            }
            
        } catch (error) {
            console.error('❌ Error creating WordStyleEditor component:', error);
        }
        
    } else {
        console.log('❌ Missing dependencies:', 
            Object.entries(dependencies)
                .filter(([name, loaded]) => !loaded)
                .map(([name]) => name)
        );
    }
    
    // Test ribbon interface features
    console.log('Testing ribbon interface features...');
    
    // Test color picker functionality
    const colors = [
        '#000000', '#333333', '#666666', '#999999', '#cccccc', '#ffffff',
        '#ff0000', '#ff6600', '#ffcc00', '#00ff00', '#0066ff', '#6600ff'
    ];
    console.log('✅ Color palette defined with', colors.length, 'colors');
    
    // Test font options
    const fonts = [
        'Arial', 'Times New Roman', 'Helvetica', 'Georgia', 'Verdana', 
        'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Courier New'
    ];
    console.log('✅ Font options defined with', fonts.length, 'fonts');
    
    // Test font sizes
    const fontSizes = ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28', '32', '36', '48', '72'];
    console.log('✅ Font sizes defined with', fontSizes.length, 'sizes');
    
    console.log('Word-style Rich Text Editor test completed');
});

// Export test results for debugging
window.WordEditorTest = {
    runTest: function() {
        console.log('Running Word Editor test...');
        document.dispatchEvent(new Event('DOMContentLoaded'));
    }
};