@props([
    'interpretacao' => null,
    'turma' => null,
])

@php
    $interp = $interpretacao;
    $ajuda = config('interpretacao_ajuda');
    $turmaSelecionada = filled($turma);
@endphp

<div class="eficacia-interpretacao" id="eficaciaInterpretacaoPanel" @if(!$turmaSelecionada) style="display: none;" @endif>
    <header class="eficacia-interpretacao__header">
        <h2 class="eficacia-interpretacao__title">Resultados gerais</h2>
        <x-interpretacao-ajuda chave="painel" :ajuda="$ajuda" />
    </header>

    <div id="eficaciaInterpretacaoContent" class="eficacia-interpretacao__body">
        @if ($interp)
            @include('components.partials.eficacia-interpretacao-body', ['interpretacao' => $interp])
        @elseif ($turmaSelecionada)
            <p class="text-muted mb-0 interpretacao-carregando">Carregando interpretação da turma…</p>
        @endif
    </div>
</div>
