/**
 * Test script for News Views functionality
 * Run this in browser console to test the multiple views system
 */

function testNewsViews() {
    console.log('Testing News Views System...');
    
    // Test 1: Check if NewsViewManager is available
    if (typeof window.NewsViewManager === 'undefined') {
        console.error('❌ NewsViewManager not found');
        return false;
    }
    console.log('✅ NewsViewManager class available');
    
    // Test 2: Check if view manager instance exists
    if (!window.newsViewManager) {
        console.error('❌ newsViewManager instance not found');
        return false;
    }
    console.log('✅ newsViewManager instance exists');
    
    // Test 3: Check initial view
    const currentView = window.newsViewManager.getCurrentView();
    console.log(`✅ Current view: ${currentView}`);
    
    // Test 4: Check if toggle buttons exist
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (!gridBtn || !listBtn) {
        console.error('❌ Toggle buttons not found');
        return false;
    }
    console.log('✅ Toggle buttons found');
    
    // Test 5: Check if container has correct classes
    const container = document.querySelector('.news-container');
    if (!container) {
        console.error('❌ News container not found');
        return false;
    }
    
    const hasViewClass = container.classList.contains('grid-view') || container.classList.contains('list-view');
    if (!hasViewClass) {
        console.error('❌ Container missing view class');
        return false;
    }
    console.log('✅ Container has correct view class');
    
    // Test 6: Test view switching
    console.log('Testing view switching...');
    
    const originalView = window.newsViewManager.getCurrentView();
    const targetView = originalView === 'grid' ? 'list' : 'grid';
    
    // Switch view
    window.newsViewManager.setViewProgrammatically(targetView);
    
    setTimeout(() => {
        const newView = window.newsViewManager.getCurrentView();
        if (newView === targetView) {
            console.log(`✅ View switched successfully to ${targetView}`);
            
            // Switch back
            window.newsViewManager.setViewProgrammatically(originalView);
            
            setTimeout(() => {
                const finalView = window.newsViewManager.getCurrentView();
                if (finalView === originalView) {
                    console.log(`✅ View switched back to ${originalView}`);
                } else {
                    console.error(`❌ Failed to switch back to ${originalView}`);
                }
            }, 100);
        } else {
            console.error(`❌ Failed to switch to ${targetView}`);
        }
    }, 100);
    
    // Test 7: Test localStorage
    try {
        const storedView = localStorage.getItem('news-view-preference');
        console.log(`✅ localStorage working, stored view: ${storedView}`);
    } catch (e) {
        console.warn('⚠️ localStorage not available:', e.message);
    }
    
    // Test 8: Test CSS classes
    const cssFiles = [
        '/css/news-views.css',
        '/css/news-cards.css'
    ];
    
    cssFiles.forEach(file => {
        const link = document.querySelector(`link[href*="${file}"]`);
        if (link) {
            console.log(`✅ CSS file loaded: ${file}`);
        } else {
            console.warn(`⚠️ CSS file not found: ${file}`);
        }
    });
    
    console.log('🎉 News Views System test completed!');
    return true;
}

// Test dropdown functionality
function testDropdowns() {
    console.log('Testing dropdown functionality...');
    
    const dropdownTriggers = document.querySelectorAll('.actions-trigger');
    if (dropdownTriggers.length === 0) {
        console.warn('⚠️ No dropdown triggers found');
        return;
    }
    
    console.log(`✅ Found ${dropdownTriggers.length} dropdown triggers`);
    
    // Test first dropdown
    const firstTrigger = dropdownTriggers[0];
    const noticiaId = firstTrigger.closest('.news-card')?.querySelector('[id*="dropdown-"]')?.id?.replace('dropdown-', '');
    
    if (noticiaId) {
        console.log(`Testing dropdown for noticia ID: ${noticiaId}`);
        
        // Test toggle function
        if (typeof window.toggleDropdown === 'function') {
            console.log('✅ toggleDropdown function available');
            
            // Test opening dropdown
            window.toggleDropdown(noticiaId);
            const dropdown = document.getElementById(`dropdown-${noticiaId}`);
            
            if (dropdown && !dropdown.classList.contains('hidden')) {
                console.log('✅ Dropdown opened successfully');
                
                // Close it
                window.toggleDropdown(noticiaId);
                
                if (dropdown.classList.contains('hidden')) {
                    console.log('✅ Dropdown closed successfully');
                } else {
                    console.error('❌ Failed to close dropdown');
                }
            } else {
                console.error('❌ Failed to open dropdown');
            }
        } else {
            console.error('❌ toggleDropdown function not found');
        }
    }
}

// Test responsive behavior
function testResponsive() {
    console.log('Testing responsive behavior...');
    
    const container = document.querySelector('.news-container');
    const cards = document.querySelectorAll('.news-card');
    
    if (!container || cards.length === 0) {
        console.warn('⚠️ No cards found for responsive test');
        return;
    }
    
    // Test different viewport sizes
    const viewports = [
        { width: 320, name: 'Mobile' },
        { width: 768, name: 'Tablet' },
        { width: 1024, name: 'Desktop' },
        { width: 1440, name: 'Large Desktop' }
    ];
    
    console.log('Testing different viewport sizes...');
    
    viewports.forEach(viewport => {
        // Simulate viewport change
        const mediaQuery = `(max-width: ${viewport.width}px)`;
        console.log(`${viewport.name} (${viewport.width}px): Media query would be ${mediaQuery}`);
    });
    
    console.log('✅ Responsive test completed (simulated)');
}

// Run all tests
function runAllTests() {
    console.log('🚀 Starting comprehensive News Views tests...');
    console.log('==========================================');
    
    testNewsViews();
    
    setTimeout(() => {
        testDropdowns();
    }, 500);
    
    setTimeout(() => {
        testResponsive();
    }, 1000);
    
    setTimeout(() => {
        console.log('==========================================');
        console.log('🏁 All tests completed!');
        console.log('Check console for any errors or warnings.');
    }, 1500);
}

// Auto-run tests if this script is loaded directly
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(runAllTests, 1000);
    });
} else {
    setTimeout(runAllTests, 1000);
}

// Export test functions for manual use
window.testNewsViews = testNewsViews;
window.testDropdowns = testDropdowns;
window.testResponsive = testResponsive;
window.runAllTests = runAllTests;