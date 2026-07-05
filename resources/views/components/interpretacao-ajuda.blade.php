@props([
    'chave',
    'ajuda' => null,
])

@php
    $textos = $ajuda ?? config('interpretacao_ajuda', []);
    $texto = $textos[$chave] ?? '';
@endphp

@if ($texto)
<button
    type="button"
    class="btn-interpretacao-ajuda"
    tabindex="0"
    data-bs-toggle="popover"
    data-bs-trigger="click"
    data-bs-placement="top"
    data-bs-custom-class="popover-interpretacao"
    data-bs-content="{{ e($texto) }}"
    aria-label="Explicação"
>?</button>
@endif
