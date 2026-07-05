@extends('layouts.app')

@section('title', 'Intervenções')

@section('content')

@php use Illuminate\Support\Str; @endphp

<div class="page-shell">

<x-page-header title="Intervenções" subtitle="Intervenções cadastradas e seus detalhes." />

@if(!empty($semIntervencoes))
    <x-empty-state
        title="Nenhuma intervenção cadastrada"
        text="Você ainda não configurou cenários de avaliação. Leia a intervenção pedagógica e defina o primeiro cenário."
        action-label="Intervenção pedagógica"
        :action-url="route('intervencoes.create')"
        icon="📚"
    />
@endif

{{-- HEADER DA PÁGINA --}}
<div class="intervencoes-toolbar">
    <div class="header-actions header-actions-compact">
        <div class="search-box" id="intervencoesSearchBox">
            <input type="text" id="intervencoesSearchInput" placeholder="Buscar por título, turma, status..." aria-label="Buscar intervenções">
            <button type="button" id="intervencoesSearchBtn" class="btn btn-dark" aria-label="Buscar">Buscar</button>
        </div>
    </div>

    <a href="{{ route('intervencoes.create') }}" class="btn btn-dark">
        Intervenção pedagógica
    </a>
</div>

{{-- TABELA DE INTERVENÇÕES --}}
<div class="table-responsive">
    <table class="table table-hover intervencoes-table">
        <thead class="table-light">
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th>Atividade</th>

                <th>Data início</th>
                <th>Data final</th>
                <th>Turma</th>
                <th>Status</th>
                <th>Cenário</th>
                <th>Dados</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($intervencoes as $intervencao)
    <tr 
        data-id="{{ $intervencao->id }}"
        data-titulo="{{ $intervencao->titulo }}"
        data-descricao="{{ $intervencao->descricao }}"
        data-tipo="{{ $intervencao->tipo }}"
        data-turma="{{ $intervencao->turma }}"
        data-status="{{ $intervencao->status }}"
        data-inicio="{{ \Carbon\Carbon::parse($intervencao->data_inicio)->format('d/m/Y') }}"
        data-fim="{{ \Carbon\Carbon::parse($intervencao->data_fim)->format('d/m/Y') }}"
        data-link="{{ $intervencao->link }}"
    >
        <td>{{ $intervencao->id }}</td>

        <td>{{ $intervencao->titulo }}</td>

        <td>{{ $intervencao->tipo ?? $intervencao->tipo_atividade }}</td>

        <td>{{ \Carbon\Carbon::parse($intervencao->data_inicio)->format('d/m/Y') }}</td>

        <td>{{ \Carbon\Carbon::parse($intervencao->data_fim)->format('d/m/Y') }}</td>

        <td>
            @if($intervencao->turma)
                <a href="#" class="turma-link" data-turma="{{ $intervencao->turma }}">{{ $intervencao->turma }}</a>
            @else
                &mdash;
            @endif
        </td>

        <td>
            <x-status-badge :status="$intervencao->status" />
        </td>

        <td>
            @if($intervencao->cenario)
                <x-cenario-badge :cenario="$intervencao->cenario" />
            @else
                &mdash;
            @endif
        </td>

        <td>
            @if($intervencao->dados_gerados_at)
                <span class="badge bg-primary">Processado</span>
            @else
                <span class="badge bg-light text-dark border">Pendente</span>
            @endif
            @if($intervencao->avaliacoes_pos_count ?? 0)
                <small class="text-muted d-block">{{ $intervencao->avaliacoes_pos_count }} aval. pós</small>
            @endif
        </td>

        <td>
            <button
                type="button"
                class="btn btn-sm btn-dark open-intervencao-btn"
                data-bs-toggle="modal"
                data-bs-target="#modalIntervencao"
                aria-label="Detalhar intervenção"
            >
                Detalhar
            </button>
        </td>
    </tr>
@endforeach

        <tr id="intervencoesSearchEmptyRow" class="d-none">
            <td colspan="10" class="text-center text-muted">Nenhuma intervenção encontrada.</td>
        </tr>

    
        </tbody>
    </table>
</div>

@if ($intervencoes->hasPages())
<div class="pagination-clean">

    <div class="pagination-info">
        {{ $intervencoes->firstItem() }}–{{ $intervencoes->lastItem() }}
        de {{ $intervencoes->total() }} resultados
    </div>

    <div class="pagination-arrows">
        @if ($intervencoes->onFirstPage())
            <span class="arrow disabled">←</span>
        @else
            <a href="{{ $intervencoes->previousPageUrl() }}" class="arrow">←</a>
        @endif

        @if ($intervencoes->hasMorePages())
            <a href="{{ $intervencoes->nextPageUrl() }}" class="arrow">→</a>
        @else
            <span class="arrow disabled">→</span>
        @endif
    </div>

</div>
@endif



<div class="modal fade" id="modalIntervencao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p><strong>Descrição:</strong></p>
                <p id="modalDescricao"></p>

                <hr>

                <p><strong>Tipo de atividade:</strong> <span id="modalTipo"></span></p>
                <p><strong>Data início:</strong> <span id="modalInicio"></span></p>
                <p><strong>Data final:</strong> <span id="modalFim"></span></p>

                <p>
                    <strong>Link:</strong>
                    <a href="#" id="modalLink" target="_blank"></a>
                </p>

                <p>
                    <strong>Turma:</strong>
                    <a href="#" id="modalTurmaLink"></a>
                </p>
            </div>

        </div>
    </div>
</div>


        <!-- Modal da Turma: exibe alunos fictícios -->
        <div class="modal fade" id="modalTurma" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTurmaTitulo"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p><strong>Alunos (fictício):</strong></p>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Aluno</th>
                                        <th>Adesão</th>
                                        <th>Aderência</th>
                                        <th>Temp. Início</th>
                                        <th>Temp. Finalização</th>
                                        <th>Desempenho</th>
                                    </tr>
                                </thead>
                                <tbody id="modalTurmaAlunosBody">
                                    <!-- preenchido via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        </div>

@endsection
