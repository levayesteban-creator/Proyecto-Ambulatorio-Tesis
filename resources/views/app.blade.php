<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SistemaMed') }}</title>

        <!-- Fuentes: DM Sans (UI) + DM Mono (datos clínicos y numéricos) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicon / Web App -->
        <link rel="icon" type="image/svg+xml" href="/icon.svg">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/icon.svg">
        <link rel="manifest" href="/manifest.json">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="antialiased" style="font-family:'DM Sans',sans-serif;background:#F1F5F9">
        @inertia
    </body>
</html>
