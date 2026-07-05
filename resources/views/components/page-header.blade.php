@props([
    'title',
    'subtitle' => null,
])

<header {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="page-header__main">
        <h1 class="page-header__title">{{ $title }}</h1>
        @if($subtitle)
            <p class="page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim($slot ?? '') !== '')
        <div class="page-header__actions">
            {{ $slot }}
        </div>
    @endif
</header>
