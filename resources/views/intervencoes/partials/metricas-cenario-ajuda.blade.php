@php
    $ajuda = config('metricas_cenario');
@endphp

<div class="metricas-cenario-ajuda mb-4">
    <p class="metricas-cenario-ajuda__intro small text-muted mb-2">{{ $ajuda['intro'] }}</p>
    <p class="metricas-cenario-ajuda__cenarios small text-muted mb-3">{{ $ajuda['cenarios'] }}</p>

    <div class="metricas-cenario-ajuda__destaque">
        <h2 class="metricas-cenario-ajuda__destaque-titulo h6 mb-2">{{ $ajuda['temporalidade_destaque']['titulo'] }}</h2>
        <p class="small mb-2 mb-md-3">{{ $ajuda['temporalidade_destaque']['texto'] }}</p>
        <ul class="small mb-0 ps-3">
            @foreach($ajuda['temporalidade_destaque']['itens'] as $item)
                <li class="mb-1">{{ $item }}</li>
            @endforeach
        </ul>
    </div>
</div>
