<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>My Travel</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('icon.svg') }}"/>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite('resources/js/main.ts')
{{--        @viteReactRefresh--}}
{{--        @vite('resources/js-react/app.tsx')--}}
    </head>
    <body class="bg-main">
        <div id="root"></div>
    </body>
</html>
