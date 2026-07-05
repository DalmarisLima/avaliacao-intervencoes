@props([
    'perguntaId',
    'tipo' => 'unica',
    'opcoes' => [],
    'obrigatoria' => false,
])

@php
    $inputType = $tipo === 'multipla' ? 'checkbox' : 'radio';
    $name = 'respostas['.$perguntaId.']'.($tipo === 'multipla' ? '[]' : '');
@endphp

<div {{ $attributes->merge(['class' => 'opcoes-escolha']) }} role="{{ $tipo === 'multipla' ? 'group' : 'radiogroup' }}">
    @foreach($opcoes as $opcao)
        @php($inputId = 'p'.$perguntaId.'_'.$loop->index)
        <label class="opcao-escolha" for="{{ $inputId }}">
            <input class="opcao-escolha__input"
                   type="{{ $inputType }}"
                   name="{{ $name }}"
                   value="{{ $opcao }}"
                   id="{{ $inputId }}"
                   @if($obrigatoria && $tipo === 'unica' && $loop->first) required @endif>
            <span class="opcao-escolha__indicador" aria-hidden="true"></span>
            <span class="opcao-escolha__texto">{{ $opcao }}</span>
        </label>
    @endforeach
</div>
