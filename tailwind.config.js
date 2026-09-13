import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './public/js/**/*.js',
    ],

    darkMode: 'class', // Habilitar modo oscuro basado en clase

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Colores personalizados para UHTV
                'uhtv-purple': {
                    50: '#f3f1ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                'uhtv-red': {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.6s ease-in-out',
                'slide-up': 'slideUp 0.6s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },

    plugins: [
        forms,
        // Plugin personalizado para utilidades adicionales
        function({ addUtilities }) {
            const newUtilities = {
                '.line-clamp-2': {
                    display: '-webkit-box',
                    '-webkit-line-clamp': '2',
                    '-webkit-box-orient': 'vertical',
                    overflow: 'hidden',
                },
                '.line-clamp-3': {
                    display: '-webkit-box',
                    '-webkit-line-clamp': '3',
                    '-webkit-box-orient': 'vertical',
                    overflow: 'hidden',
                },
                '.gradient-text': {
                    background: 'linear-gradient(135deg, #7c3aed, #dc2626)',
                    '-webkit-background-clip': 'text',
                    '-webkit-text-fill-color': 'transparent',
                    'background-clip': 'text',
                },
            }
            addUtilities(newUtilities)
        }
    ],
};
