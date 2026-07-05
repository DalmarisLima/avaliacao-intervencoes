@php
    $interp = $interpretacao;
    $ajuda = config('interpretacao_ajuda');
    $sintese = str_replace(['**', '*'], '', $interp['sintese'] ?? '');
    $insight = trim($interp['insight_principal'] ?? '');
    $textoResumo = $sintese !== '' ? $sintese : $insight;
    $vereditoClass = ($interp['eficaz'] ?? null) === true ? 'ok' : (($interp['eficaz'] ?? null) === false ? 'fail' : 'neutral');

    $dimAjudaKey = static function (string $titulo): string {
        $t = mb_strtolower($titulo);
        if (str_contains($t, 'aprendizagem')) {
            return 'dim_aprendizagem';
        }
        if (str_contains($t, 'interven')) {
            return 'dim_intervencoes';
        }
        if (str_contains($t, 'processo')) {
            return 'dim_processo';
        }
        if (str_contains($t, 'meta')) {
            return 'dim_meta';
        }

        return 'dim_generico';
    };
@endphp

<div class="interpretacao-layout">
    <section class="interpretacao-bloco interpretacao-bloco--resumo">
        <div class="interpretacao-bloco__head">
            <h3 class="interpretacao-bloco__title">Resumo da turma</h3>
            <x-interpretacao-ajuda chave="conclusao" :ajuda="$ajuda" />
        </div>

        <div class="interpretacao-conclusao">
            <span class="interpretacao-veredito interpretacao-veredito--{{ $vereditoClass }}">
                {{ $interp['classificacao_rotulo'] ?? '—' }}
            </span>
            @if (isset($interp['indice_eficacia']) && ($interp['eficaz'] ?? null) !== null)
            <span class="interpretacao-indice">
                Índice {{ $interp['indice_eficacia'] }}/100
                <x-interpretacao-ajuda chave="indice" :ajuda="$ajuda" />
            </span>
            @endif
        </div>

        @if ($textoResumo !== '')
        <p class="interpretacao-resumo-texto">{{ $textoResumo }}</p>
        @endif

        @if (isset($interp['delta_desempenho']))
        <div class="interpretacao-kpis">
            <div class="interpretacao-kpi">
                <span class="interpretacao-kpi__label">
                    Pré
                    <x-interpretacao-ajuda chave="pre" :ajuda="$ajuda" />
                </span>
                <span class="interpretacao-kpi__value interpretacao-kpi__value--pre">{{ $interp['pre_desempenho'] ?? '—' }}%</span>
            </div>
            <div class="interpretacao-kpi">
                <span class="interpretacao-kpi__label">
                    Pós
                    <x-interpretacao-ajuda chave="pos" :ajuda="$ajuda" />
                </span>
                <span class="interpretacao-kpi__value interpretacao-kpi__value--pos">{{ $interp['pos_desempenho'] ?? '—' }}%</span>
            </div>
            <div class="interpretacao-kpi">
                <span class="interpretacao-kpi__label">
                    Ganho
                    <x-interpretacao-ajuda chave="ganho" :ajuda="$ajuda" />
                </span>
                <span class="interpretacao-kpi__value {{ ($interp['delta_desempenho'] ?? 0) > 0 ? 'is-up' : (($interp['delta_desempenho'] ?? 0) < 0 ? 'is-down' : '') }}">
                    {{ ($interp['delta_desempenho'] ?? 0) > 0 ? '+' : '' }}{{ $interp['delta_desempenho'] }} pts
                </span>
            </div>
        </div>
        @endif
    </section>

    @if (!empty($interp['camadas']))
    <section class="interpretacao-bloco">
        <div class="interpretacao-bloco__head">
            <h3 class="interpretacao-bloco__title">Por dimensão</h3>
            <x-interpretacao-ajuda chave="dimensoes" :ajuda="$ajuda" />
        </div>
        <div class="interpretacao-dimensoes">
            @foreach ($interp['camadas'] as $camada)
            <article class="interpretacao-dim interpretacao-dim--{{ $camada['status'] ?? 'neutro' }}">
                <header class="interpretacao-dim__head">
                    <h4 class="interpretacao-dim__title">{{ $camada['titulo'] }}</h4>
                    <x-interpretacao-ajuda :chave="$dimAjudaKey($camada['titulo'])" :ajuda="$ajuda" />
                </header>
                <p class="interpretacao-dim__texto">{{ $camada['texto'] }}</p>
            </article>
            @endforeach
        </div>
    </section>
    @endif

    @if (!empty($interp['intervencoes']))
    <details class="interpretacao-bloco interpretacao-bloco--collapsible">
        <summary class="interpretacao-bloco__summary">
            Intervenções desta turma ({{ count($interp['intervencoes']) }})
            <x-interpretacao-ajuda chave="intervencoes_lista" :ajuda="$ajuda" />
        </summary>
        <div class="eficacia-interpretacao__lista">
            @foreach ($interp['intervencoes'] as $item)
            @php
                $itemVeredito = ($item['eficaz'] ?? null) === true ? 'ok' : (($item['eficaz'] ?? null) === false ? 'fail' : 'neutral');
            @endphp
            <div class="eficacia-intervencao-item">
                <div class="eficacia-intervencao-item__header">
                    <strong>{{ $item['titulo'] ?? 'Intervenção' }}</strong>
                    <span class="interpretacao-veredito interpretacao-veredito--{{ $itemVeredito }}">
                        {{ $item['classificacao_rotulo'] ?? '—' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </details>
    @endif
</div>
