@extends('layouts.app')

@section('title', 'Avaliação da Intervenção')

@section('content')

<div class="content-container">

    {{-- HEADER --}}
    <div class="page-title-block is-form">
        <h1 class="page-title">Avaliação da intervenção</h1>
        <p class="page-subtitle">{{ $intervencao->titulo }}</p>
    </div>

    {{-- CARD --}}
   <div class="form-wrapper">
        <div class="form-card">

        <form
            method="POST"
            action="{{ route('avaliacoes.store', $intervencao->id) }}"
        >
            @csrf

            {{-- CENÁRIO --}}
            <div class="form-group">
                <label>
                    Cenário <span class="required">*</span>
                </label>

                <div class="radio-group">
                    <label>
                        <input type="radio" name="cenario" value="flexivel" required>
                        Flexível
                    </label>

                    <label>
                        <input type="radio" name="cenario" value="moderado">
                        Moderado
                    </label>

                    <label>
                        <input type="radio" name="cenario" value="dificil">
                        Difícil
                    </label>
                </div>
            </div>

            <hr>

            <strong>Métricas:</strong>

            {{-- ADESÃO: agora sim/nao --}}
            <div class="form-group form-group-inline">
                <label>Adesão: <span class="required">*</span></label>

                <div class="radio-group">
                    <label>
                        <input type="radio" name="adesao" value="sim" required>
                        Sim
                    </label>

                    <label>
                        <input type="radio" name="adesao" value="nao">
                        Não
                    </label>
                </div>
            </div>

            {{-- ADERÊNCIA --}}
            <div class="slider-row">
                <span class="slider-label">Aderência:</span>

                <input
                    type="range"
                    name="aderencia"
                    class="form-range"
                    min="0"
                    max="100"
                    value="0"
                    required
                >

                <span class="slider-value">0%</span>
            </div>

            {{-- TEMPORALIDADE DE INÍCIO --}}
            <div class="slider-row">
                <span class="slider-label">Tempo para iniciar (min):</span>

                <input
                    type="range"
                    name="temporalidade_inicio"
                    class="form-range"
                    min="0"
                    max="240"
                    value="0"
                    required
                >

                <span class="slider-value">0 min</span>
            </div>

            {{-- TEMPORALIDADE DE FINALIZAÇÃO --}}
            <div class="slider-row">
                <span class="slider-label">Tempo para finalizar (min):</span>

                <input
                    type="range"
                    name="temporalidade_fim"
                    class="form-range"
                    min="0"
                    max="240"
                    value="0"
                    required
                >

                <span class="slider-value">0 min</span>
            </div>

            {{-- DESEMPENHO --}}
            <div class="slider-row">
                <span class="slider-label">Desempenho:</span>

                <input
                    type="range"
                    name="desempenho"
                    class="form-range"
                    min="0"
                    max="100"
                    value="0"
                    required
                >

                <span class="slider-value">0%</span>
            </div>

          

            {{-- AÇÕES --}}
            <div class="avaliacao-actions">
                <button type="submit" class="btn btn-dark">
                    Salvar
                </button>

                <a href="{{ route('intervencoes.index') }}" class="btn btn-outline-dark">
                    Cancelar
                </a>
            </div>

        </form>
        </div>
    </div>
</div>

@endsection
