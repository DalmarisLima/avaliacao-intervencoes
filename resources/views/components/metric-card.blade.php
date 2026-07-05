@props([
    'label',
    'value',
    'suffix' => '',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'metric-card']) }}>
    <div class="metric-card__label">{{ $label }}</div>
    <div class="metric-card__value">{{ $value }}{{ $suffix }}</div>
    @if ($hint)
        <p class="metric-card__hint">{{ $hint }}</p>
    @endif
</div>
