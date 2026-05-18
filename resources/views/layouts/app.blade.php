<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sorakaro') }}</title>

        <!-- Enable native cross-document view transitions for supported browsers -->
        <meta name="view-transition" content="same-origin">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Livewire -->
        @livewireStyles
    </head>

    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        @include('layouts.sidebar', [
            'header' => $header ?? null,
            'slot' => $slot
        ])

        <!-- Livewire -->
        @livewireScripts

        @stack('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Intercept links to add a smooth fade-out transition
                document.querySelectorAll('a[href]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        const href = link.getAttribute('href');
                        
                        // Ignore hash links, external links, and new tabs
                        if (!href || href.startsWith('#') || link.target === '_blank' || e.ctrlKey || e.metaKey || link.hasAttribute('download')) {
                            return;
                        }
                        
                        // If it's a valid local link, prevent default, fade out, then navigate
                        const targetUrl = link.href;
                        if (targetUrl && targetUrl.startsWith(window.location.origin)) {
                            // Check if there is an onclick handler or Livewire attributes
                            if (link.onclick || link.hasAttribute('wire:navigate')) return;
                            
                            e.preventDefault();
                            const mainContent = document.querySelector('main');
                            if (mainContent) {
                                mainContent.style.transition = 'opacity 0.25s ease-out, transform 0.25s ease-out';
                                mainContent.style.opacity = '0';
                                mainContent.style.transform = 'translateY(4px)';
                            }
                            
                            setTimeout(() => {
                                window.location.href = targetUrl;
                            }, 200);
                        }
                    });
                });
            });

            // Handle back button cache (BFCache)
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) {
                    const mainContent = document.querySelector('main');
                    if (mainContent) {
                        mainContent.style.transition = 'none';
                        mainContent.style.opacity = '1';
                        mainContent.style.transform = 'none';
                    }
                }
            });
        </script>
    </body>
</html>
