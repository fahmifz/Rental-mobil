<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Awal Putra Rentcar' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/hero/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (() => {
            document.documentElement.classList.toggle('dark', localStorage.getItem('theme') === 'dark');
            document.documentElement.setAttribute('dir', localStorage.getItem('dir') === 'rtl' ? 'rtl' : 'ltr');
        })();
    </script>
</head>

<body class="bg-white font-outfit text-gray-800 antialiased dark:bg-gray-900 dark:text-white/90">
    @yield('content')
</body>

</html>
