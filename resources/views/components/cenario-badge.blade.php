@props(['cenario'])

@php
    $normalizado = strtolower(trim((string) $cenario));
    $normalizado = str_replace(
        ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'],
        ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'],
        $normalizado
    );
    $classe = match (true) {
        str_contains($normalizado, 'flex') || $normalizado === 'leve' => 'flexivel',
        str_contains($normalizado, 'moder') || str_contains($normalizado, 'rig') || str_contains($normalizado, 'model') => 'moderado',
        str_contains($normalizado, 'dific') || str_contains($normalizado, 'personal') => 'dificil',
        default => 'flexivel',
    };
    $rotulo = match ($classe) {
        'flexivel' => 'Flexível',
        'moderado' => 'Moderado',
        'dificil' => 'Difícil',
        default => 'Cenário',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge-cenario badge-cenario--{$classe}"]) }}>
    {{ $rotulo }}
</span>
