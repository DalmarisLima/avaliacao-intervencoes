@props([
    'title',
    'text' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'icon' => '📋',
])

<div class="empty-state" role="status">
    <div class="empty-state__icon" aria-hidden="true">{{ $icon }}</div>
    <h2 class="empty-state__title">{{ $title }}</h2>
    @if ($text)
        <p class="empty-state__text">{{ $text }}</p>
    @endif
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary">{{ $actionLabel }}</a>
    @endif
    {{ $slot }}
</div>
