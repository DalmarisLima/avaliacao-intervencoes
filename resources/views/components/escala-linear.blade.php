@props([
    'min' => 1,
    'max' => 5,
    'minRotulo' => null,
    'maxRotulo' => null,
    'perguntaId' => null,
    'obrigatoria' => false,
    'preview' => false,
])

@php
    $temRotuloMin = filled($minRotulo);
    $temRotuloMax = filled($maxRotulo);
@endphp

<div {{ $attributes->merge(['class' => 'escala-linear']) }} role="group" aria-label="Escala de {{ $min }} a {{ $max }}">
    @if($temRotuloMin)
        <span class="escala-linear__rotulo escala-linear__rotulo--min">{{ $minRotulo }}</span>
    @endif

    <div class="escala-linear__opcoes">
        @for($valor = $min; $valor <= $max; $valor++)
            @if($preview)
                <span class="escala-linear__valor escala-linear__valor--preview">{{ $valor }}</span>
            @else
                <label class="escala-linear__valor">
                    <input type="radio"
                           name="respostas[{{ $perguntaId }}]"
                           value="{{ $valor }}"
                           class="escala-linear__input"
                           @if($obrigatoria && $valor === $min) required @endif>
                    <span class="escala-linear__face" aria-hidden="true">{{ $valor }}</span>
                </label>
            @endif
        @endfor
    </div>

    @if($temRotuloMax)
        <span class="escala-linear__rotulo escala-linear__rotulo--max">{{ $maxRotulo }}</span>
    @endif
</div>
