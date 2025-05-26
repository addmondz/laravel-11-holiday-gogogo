<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <link rel="icon" type="image/png" sizes="192x192"  href="/images/cropped-apple-touch-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="180x180" href="/images/cropped-apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/cropped-apple-touch-icon-32x32.png">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased light">
        @inertia
    </body>
</html>
