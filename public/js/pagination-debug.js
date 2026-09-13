/**
 * Pagination Debug Script
 * Helps identify pagination loading issues
 */

console.log('🔍 Pagination Debug Script Loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Content Loaded');
    
    // Check for pagination elements
    const paginationWrapper = document.querySelector('.enhanced-pagination-wrapper');
    const paginationNav = document.querySelector('.enhanced-pagination');
    const paginationLinks = document.querySelectorAll('.enhanced-pagination .page-link');
    
    console.log('🔗 Pagination Elements Found:', {
        wrapper: !!paginationWrapper,
        nav: !!paginationNav,
        links: paginationLinks.length
    });
    
    // Monitor pagination link clicks
    paginationLinks.forEach((link, index) => {
        link.addEventListener('click', function(e) {
            console.log(`🖱️ Pagination Link ${index + 1} Clicked:`, {
                href: this.href,
                text: this.textContent.trim(),
                disabled: this.closest('.page-item.disabled'),
                active: this.closest('.page-item.active')
            });
            
            // Check if it's a valid navigation
            if (!this.closest('.page-item.disabled') && !this.closest('.page-item.active')) {
                console.log('✅ Valid navigation - proceeding');
            } else {
                console.log('❌ Invalid navigation - blocked');
                e.preventDefault();
            }
        });
    });
    
    // Monitor page size changes
    const pageSizeSelect = document.getElementById('page-size');
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function(e) {
            console.log('📊 Page Size Changed:', {
                oldValue: this.defaultValue,
                newValue: this.value
            });
        });
    }
    
    // Monitor page jump
    const pageJumpInput = document.getElementById('page-jump-input');
    const pageJumpBtn = document.querySelector('.page-jump .btn');
    
    if (pageJumpInput) {
        pageJumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                console.log('⏎ Page Jump Enter Pressed:', {
                    value: this.value,
                    max: this.getAttribute('max'),
                    min: this.getAttribute('min')
                });
            }
        });
    }
    
    if (pageJumpBtn) {
        pageJumpBtn.addEventListener('click', function(e) {
            console.log('🔘 Page Jump Button Clicked');
        });
    }
    
    // Monitor for JavaScript errors
    window.addEventListener('error', function(e) {
        console.error('❌ JavaScript Error:', {
            message: e.message,
            filename: e.filename,
            lineno: e.lineno,
            colno: e.colno
        });
    });
    
    // Monitor for unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
        console.error('❌ Unhandled Promise Rejection:', e.reason);
    });
    
    // Check for conflicting scripts
    const scripts = Array.from(document.querySelectorAll('script[src]'));
    const paginationScripts = scripts.filter(script => 
        script.src.includes('pagination') || 
        script.src.includes('enhanced')
    );
    
    console.log('📜 Pagination-related Scripts:', paginationScripts.map(s => s.src));
    
    // Check for duplicate functions
    const duplicateFunctions = [];
    if (typeof window.changePageSize !== 'undefined') duplicateFunctions.push('changePageSize');
    if (typeof window.jumpToPage !== 'undefined') duplicateFunctions.push('jumpToPage');
    
    if (duplicateFunctions.length > 0) {
        console.warn('⚠️ Duplicate Functions Found:', duplicateFunctions);
    }
    
    console.log('✅ Pagination Debug Setup Complete');
});