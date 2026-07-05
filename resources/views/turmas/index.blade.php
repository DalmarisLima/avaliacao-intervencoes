@extends('layouts.app')

@section('title', 'Turmas')

@section('content')

<div class="content-container">

    <div class="page-title-block">
        <h1 class="page-title">Turmas</h1>
        <p class="page-subtitle">Visão geral das turmas e indicadores consolidados.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Turma</th>
                    <th>Intervenções</th>
                    <th>Adesão</th>
                    <th>Aderência</th>
                    <th>Temp. Início</th>
                    <th>Temp. Finalização</th>
                    <th>Desempenho</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($turmas as $turma)
                    @php $s = $statsByTurma[$turma] ?? null; @endphp
                    <tr data-turma="{{ $turma }}">
                        <td><strong>{{ $turma }}</strong></td>
                        <td>
                            @php $count = $intervCounts[$turma] ?? 0; @endphp
                            <strong>{{ $count }}</strong>
                        </td>
                        <td>
                            <span class="badge {{ isset($s->adesao_avg) ? 'bg-success' : 'bg-secondary' }}">
                                {{ isset($s->adesao_avg) ? (round($s->adesao_avg * 100) . '%') : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ isset($s->aderencia_avg) ? 'bg-info' : 'bg-secondary' }}">
                                {{ isset($s->aderencia_avg) ? round($s->aderencia_avg) . '%' : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ isset($s->temporalidade_inicio_avg) ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                {{ isset($s->temporalidade_inicio_avg) ? round($s->temporalidade_inicio_avg) . ' min' : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ isset($s->temporalidade_fim_avg) ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                {{ isset($s->temporalidade_fim_avg) ? round($s->temporalidade_fim_avg) . ' min' : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ isset($s->desempenho_avg) ? 'bg-primary' : 'bg-secondary' }}">
                                {{ isset($s->desempenho_avg) ? round($s->desempenho_avg) . '%' : '—' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-dark show-intervencoes-btn" data-turma="{{ $turma }}" type="button">
                                Ver intervenções
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- Modal: intervenções detalhadas da turma -->
<div class="modal fade" id="modalIntervencoesTurma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-intervencoes-turma">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalIntervencoesTurmaTitulo" class="modal-title">Intervenções da turma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-card">
                    <table class="table table-sm table-hover align-middle text-nowrap-desktop">
                        <thead class="table-light">
                            <tr>
                                <th>Intervenção</th>
                                <th>Adesão (Pós)</th>
                                <th>Aderência (Pré → Pós)</th>
                                <th>Temp. Início (Pré → Pós)</th>
                                <th>Temp. Finalização (Pré → Pós)</th>
                                <th>Desempenho (Pré → Pós)</th>
                            </tr>
                        </thead>
                        <tbody id="modalIntervencoesTurmaBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
