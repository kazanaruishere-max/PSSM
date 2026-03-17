<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Ensure text is always visible on light background, even if browser forces dark mode colors */
            body {
                color: #1a202c !important; /* text-gray-900 */
                background-color: #f7fafc !important; /* bg-gray-100 */
            }
            .bg-white {
                background-color: #ffffff !important;
            }
            .text-gray-800 {
                color: #2d3748 !important;
            }
            .text-gray-700 {
                color: #4a5568 !important;
            }
            .text-gray-600 {
                color: #718096 !important;
            }
            .text-gray-500 {
                color: #a0aec0 !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center p-4 sm:p-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50">
            <div class="mb-6 transform hover:rotate-12 transition-transform duration-500">
                <a href="/">
                    <x-application-logo class="w-24 h-24 fill-current text-indigo-600 drop-shadow-2xl" />
                </a>
            </div>

            <div class="w-full sm:max-w-md bg-white/80 backdrop-blur-xl shadow-2xl shadow-indigo-100 overflow-hidden rounded-3xl border border-white p-8">
                {{ $slot }}
            </div>
            
            <div class="mt-12 text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                    &copy; {{ date('Y') }} PSSM - POWERED SMART SCHOOL MANAGEMENT
                </p>
            </div>
        </div>
    </body>
</html>
