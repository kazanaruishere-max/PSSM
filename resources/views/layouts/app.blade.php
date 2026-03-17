<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
        
        <!-- Lucide Icons (Shadcn style) -->
        <script src="https://unpkg.com/lucide@latest"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --background: 0 0% 100%;
                --foreground: 222.2 84% 4.9%;
                --card: 0 0% 100%;
                --card-foreground: 222.2 84% 4.9%;
                --popover: 0 0% 100%;
                --popover-foreground: 222.2 84% 4.9%;
                --primary: 222.2 47.4% 11.2%;
                --primary-foreground: 210 40% 98%;
                --secondary: 210 40% 96.1%;
                --secondary-foreground: 222.2 47.4% 11.2%;
                --muted: 210 40% 96.1%;
                --muted-foreground: 215.4 16.3% 46.9%;
                --accent: 210 40% 96.1%;
                --accent-foreground: 222.2 47.4% 11.2%;
                --destructive: 0 84.2% 60.2%;
                --destructive-foreground: 210 40% 98%;
                --border: 214.3 31.8% 91.4%;
                --input: 214.3 31.8% 91.4%;
                --ring: 222.2 84% 4.9%;
                --radius: 0.5rem;
            }

            body {
                font-family: 'Geist', sans-serif !important;
                background-color: #ffffff;
                color: #020817;
                -webkit-font-smoothing: antialiased;
            }

            /* Shadcn Scrollbar */
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        </style>
    </head>
    <body class="font-sans antialiased">
        @include('layouts.navigation')
    </body>
</html>
