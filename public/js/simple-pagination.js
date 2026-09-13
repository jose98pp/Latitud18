/**
 * Simple Pagination Script
 * Clean implementation without conflicts
 */

(function () {
    'use strict';

    // Prevent multiple initializations
    if (window.simplePaginationLoaded) {
        return;
    }
    window.simplePaginationLoaded = true;

    console.log('🚀 Simple Pagination Loading...');

    function initPagination() {
        console.log('📄 Initializing Simple Pagination');

        // Page size selector
        const pageSizeSelect = document.getElementById('page-size');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                console.log('📊 Page size changed to:', this.value);
                changePageSize(this.value);
            });
        }

        // Page jump input
        const pageJumpInput = document.getElementById('page-jump-input');
        if (pageJumpInput) {
            pageJumpInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    console.log('⏎ Enter pressed in page jump');
                    jumpToPage();
                }
            });
        }

        // Page jump button
        const pageJumpBtn = document.querySelector('.page-jump .btn');
        if (pageJumpBtn) {
            pageJumpBtn.addEventListener('click', function (e) {
                e.preventDefault();
                console.log('🔘 Page jump button clicked');
                jumpToPage();
            });
        }

        // Note: We deliberately DO NOT add click listeners to pagination links here.
        // This prevents conflicts with dynamic-filters.js which handles AJAX pagination.
        // If dynamic-filters.js is not present, standard browser navigation works fine.

        console.log('✅ Simple Pagination Initialized');
    }

    function changePageSize(perPage) {
        console.log('📊 Changing page size to:', perPage);

        try {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1);

            showSimpleLoading();
            window.location.href = url.toString();
        } catch (error) {
            console.error('❌ Error changing page size:', error);
        }
    }

    function jumpToPage() {
        console.log('🔄 Jumping to page...');

        const input = document.getElementById('page-jump-input');
        if (!input) {
            console.error('❌ Page jump input not found');
            return;
        }

        const page = parseInt(input.value);
        const maxPage = parseInt(input.getAttribute('max'));
        const minPage = parseInt(input.getAttribute('min')) || 1;

        console.log('📊 Page jump values:', { page, minPage, maxPage });

        if (!page || isNaN(page) || page < minPage || page > maxPage) {
            alert(`Por favor ingrese un número de página válido entre ${minPage} y ${maxPage}`);
            input.focus();
            input.select();
            return;
        }

        try {
            const url = new URL(window.location);
            url.searchParams.set('page', page);

            showSimpleLoading();
            window.location.href = url.toString();
        } catch (error) {
            console.error('❌ Error jumping to page:', error);
        }
    }

    function showSimpleLoading() {
        console.log('⏳ Showing loading indicator');

        // Remove existing loading indicators
        const existingLoading = document.querySelectorAll('.simple-loading-indicator');
        existingLoading.forEach(el => el.remove());

        // Create new loading indicator
        const loading = document.createElement('div');
        loading.className = 'simple-loading-indicator';
        loading.innerHTML = `
            <div style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            ">
                <div style="
                    width: 20px;
                    height: 20px;
                    border: 2px solid #f3f3f3;
                    border-top: 2px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                "></div>
                <span>Cargando página...</span>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;

        document.body.appendChild(loading);
    }

    // Make functions globally available
    window.changePageSize = changePageSize;
    window.jumpToPage = jumpToPage;

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPagination);
    } else {
        initPagination();
    }

})();