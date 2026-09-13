<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- CSS Optimization and Performance Meta Tags -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="format-detection" content="telephone=no">
        <meta name="theme-color" content="#7c3aed">
        
        <!-- Preload critical CSS for better performance -->
        <link rel="preload" href="{{ asset('css/optimized.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="{{ asset('css/optimized.css') }}"></noscript>
        
        @vite(['resources/css/app.css', 'resources/css/browser-compatibility.css', 'resources/js/app.jsx'])

        <title>{{ config('app.name', 'Admin') }}</title>

        <!-- DNS Prefetch for better performance -->
        <link rel="dns-prefetch" href="//fonts.googleapis.com">
        <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </body>
</html>
