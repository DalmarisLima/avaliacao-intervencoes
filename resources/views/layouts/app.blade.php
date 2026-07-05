<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @php
        $tokensCssV = @filemtime(public_path('css/tokens.css')) ?: time();
        $mainCssV = @filemtime(public_path('css/main.css')) ?: time();
        $sidebarCssV = @filemtime(public_path('css/sidebar.css')) ?: time();
        $formsCssV = @filemtime(public_path('css/forms.css')) ?: time();
        $componentsCssV = @filemtime(public_path('css/components.css')) ?: time();
        $uiCssV = @filemtime(public_path('css/ui.css')) ?: time();
        $responsiveCssV = @filemtime(public_path('css/responsive.css')) ?: time();
        $mainJsV = @filemtime(public_path('js/main.js')) ?: time();
    @endphp

    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}?v={{ $tokensCssV }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}?v={{ $mainCssV }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}?v={{ $sidebarCssV }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ $formsCssV }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}?v={{ $componentsCssV }}">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ $uiCssV }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ $responsiveCssV }}">

    @stack('styles')
</head>
<body>

<div class="app-shell" id="appShell">
    <button type="button" class="btn btn-outline-secondary btn-sm sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu">
        Menu
    </button>
    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
    @include('components.sidebar')

    <main class="app-main">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/main.js') }}?v={{ $mainJsV }}"></script>

@stack('scripts')
</body>
</html>
