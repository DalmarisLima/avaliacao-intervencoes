<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Entrar')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @php
        $tokensCssV = @filemtime(public_path('css/tokens.css')) ?: time();
        $mainCssV = @filemtime(public_path('css/main.css')) ?: time();
        $uiCssV = @filemtime(public_path('css/ui.css')) ?: time();
    @endphp

    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}?v={{ $tokensCssV }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}?v={{ $mainCssV }}">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ $uiCssV }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ @filemtime(public_path('css/responsive.css')) ?: time() }}">

    @stack('styles')
</head>
<body class="auth-body">
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
