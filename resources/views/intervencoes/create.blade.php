@extends('layouts.app')

@section('title', 'Intervenção pedagógica')

@section('content')
<div class="content-container">

<x-wizard-steps :current="1" />

@if(session('info'))
    <div class="alert alert-info" role="alert">{{ session('info') }}</div>
@endif

<div class="page-title-block is-form">
    <h1 class="page-title">Intervenção pedagógica</h1>
    <p class="page-subtitle">
        Etapa 1 de 3 — leia a descrição
        @if($cenarioAtual)
            do cenário <strong>{{ $cenarioAtual->rotulo() }}</strong>
        @endif
        e avance para configurar a avaliação.
    </p>
</div>

<div class="form-wrapper">
    <div class="form-card">
        @if($cenarioAtual)
            <p class="mb-3">
                <span class="badge text-bg-secondary">Cenário {{ $cenarioAtual->rotulo() }}</span>
            </p>
        @endif

        @if(filled($conteudoIntervencao))
            <div class="rich-text-content rich-text-content--document mb-4">
                {!! \App\Support\RichTextSanitizer::display($conteudoIntervencao) !!}
            </div>
        @else
            <div class="alert alert-warning mb-4" role="status">
                A descrição desta intervenção ainda não foi configurada.
            </div>
        @endif

        <form method="POST" action="{{ route('intervencoes.iniciar-cenario') }}" class="form-actions mt-4">
            @csrf
            <button type="submit" class="btn btn-dark">
                Definir cenário de avaliação
            </button>
        </form>
    </div>
</div>

</div>
@endsection
