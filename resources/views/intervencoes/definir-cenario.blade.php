@extends('layouts.app')

@section('title', 'Definir cenário')

@section('content')
<div class="content-container">
    <x-wizard-steps :current="2" />

    @if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="page-title-block is-form">
        <h1 class="page-title">Definição de cenário</h1>
        <p class="page-subtitle">Etapa 2 de 3 — selecione o cenário e ajuste os limiares de avaliação.</p>
    </div>

    @if(session('info'))
    <div class="alert alert-info" role="alert">
        {{ session('info') }}
    </div>
    @endif

    <div class="form-wrapper">
        <div class="form-card">
            <form method="POST" action="{{ route('intervencoes.salvar-cenario', $intervencao->id) }}">
                @csrf

                <div class="form-group">
                    <label>Intervenção</label>
                    <div class="text-muted">
                        <strong>{{ $intervencao->titulo }}</strong> · Turma: {{ $intervencao->turma }}
                    </div>
                </div>

                <div class="form-group">
                    <label>Tipo de cenário <span class="required">*</span></label>
                    <div class="radio-group">
                        @foreach(\App\Enums\Cenario::paraConfiguracao() as $opcaoCenario)
                            <label>
                                <input type="radio" name="cenario" value="{{ $opcaoCenario->value }}"
                                       {{ old('cenario', $cenarioSugerido ?? $intervencao->cenario) === $opcaoCenario->value ? 'checked' : '' }}
                                       @if($loop->first) required @endif>
                                {{ $opcaoCenario->rotulo() }}
                            </label>
                        @endforeach
                    </div>
                    @if(in_array(old('cenario', $intervencao->cenario), ['moderado', 'rigido', 'modelado', 'modelo'], true))
                        <p class="small text-muted mt-2 mb-0">
                            Esta intervenção foi salva com um cenário antigo (moderado). Escolha Flexível ou Difícil para atualizar.
                        </p>
                    @endif
                    @error('cenario')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @php($metricasAjuda = config('metricas_cenario.metricas'))

                @include('intervencoes.partials.metricas-cenario-ajuda')

                <h2 class="h6 fw-semibold mb-3">Limiares de avaliação</h2>

                <div class="slider-block">
                <div class="slider-row">
                    <span class="slider-label">{{ $metricasAjuda['aderencia']['label'] }}:</span>
                    <input type="range" name="limiar_aderencia" id="limiar_aderencia" class="form-range" min="0" max="100" value="{{ old('limiar_aderencia', $intervencao->limiar_aderencia ?? 25) }}" required>
                    <span class="slider-value" id="limiar_aderencia_valor">{{ old('limiar_aderencia', $intervencao->limiar_aderencia ?? 25) }}%</span>
                </div>
                <p class="slider-hint">{{ $metricasAjuda['aderencia']['hint'] }}</p>
                </div>
                @error('limiar_aderencia')
                <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div class="slider-block">
                <div class="slider-row">
                    <span class="slider-label">{{ $metricasAjuda['temporalidade_inicio']['label'] }}:</span>
                    <input type="range" name="limiar_temporalidade_inicio" id="limiar_temporalidade_inicio" class="form-range" min="0" max="240" value="{{ old('limiar_temporalidade_inicio', $intervencao->limiar_temporalidade_inicio ?? 20) }}" required>
                    <span class="slider-value" id="limiar_temporalidade_inicio_valor">{{ old('limiar_temporalidade_inicio', $intervencao->limiar_temporalidade_inicio ?? 20) }} min</span>
                </div>
                <p class="slider-hint">{{ $metricasAjuda['temporalidade_inicio']['hint'] }}</p>
                </div>
                @error('limiar_temporalidade_inicio')
                <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div class="slider-block">
                <div class="slider-row">
                    <span class="slider-label">{{ $metricasAjuda['temporalidade_fim']['label'] }}:</span>
                    <input type="range" name="limiar_temporalidade_fim" id="limiar_temporalidade_fim" class="form-range" min="0" max="240" value="{{ old('limiar_temporalidade_fim', $intervencao->limiar_temporalidade_fim ?? 60) }}" required>
                    <span class="slider-value" id="limiar_temporalidade_fim_valor">{{ old('limiar_temporalidade_fim', $intervencao->limiar_temporalidade_fim ?? 60) }} min</span>
                </div>
                <p class="slider-hint">{{ $metricasAjuda['temporalidade_fim']['hint'] }}</p>
                </div>
                @error('limiar_temporalidade_fim')
                <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <div class="slider-block">
                <div class="slider-row">
                    <span class="slider-label">{{ $metricasAjuda['desempenho']['label'] }}:</span>
                    <input type="range" name="limiar_desempenho" id="limiar_desempenho" class="form-range" min="0" max="100" value="{{ old('limiar_desempenho', $intervencao->limiar_desempenho ?? 25) }}" required>
                    <span class="slider-value" id="limiar_desempenho_valor">{{ old('limiar_desempenho', $intervencao->limiar_desempenho ?? 25) }}%</span>
                </div>
                <p class="slider-hint">{{ $metricasAjuda['desempenho']['hint'] }}</p>
                </div>
                @error('limiar_desempenho')
                <div class="text-danger small">{{ $message }}</div>
                @enderror

                <div class="form-actions">
                    <a href="{{ route('intervencoes.index') }}" class="btn btn-outline-dark">Cancelar</a>
                    <button type="submit" class="btn btn-dark">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const fields = [
        { id: 'limiar_aderencia', suffix: '%' },
        { id: 'limiar_temporalidade_inicio', suffix: ' min' },
        { id: 'limiar_temporalidade_fim', suffix: ' min' },
        { id: 'limiar_desempenho', suffix: '%' },
    ];

    const updateValue = (id, suffix) => {
        const input = document.getElementById(id);
        const output = document.getElementById(`${id}_valor`);
        if (!input || !output) return;
        output.textContent = `${input.value}${suffix}`;
    };

    fields.forEach(({ id, suffix }) => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('input', () => updateValue(id, suffix));
        updateValue(id, suffix);
    });

    const presets = {
        flexivel: { limiar_aderencia: 25, limiar_temporalidade_inicio: 20, limiar_temporalidade_fim: 60, limiar_desempenho: 25 },
        dificil: { limiar_aderencia: 80, limiar_temporalidade_inicio: 10, limiar_temporalidade_fim: 30, limiar_desempenho: 80 },
    };

    const radios = document.querySelectorAll('input[name="cenario"]');
    const fromOldInput = {{ old('cenario') ? 'true' : 'false' }};

    const applyPreset = (cenario) => {
        if (!presets[cenario]) return;
        Object.entries(presets[cenario]).forEach(([id, value]) => {
            const input = document.getElementById(id);
            if (!input) return;
            input.value = value;
            const suffix = id.includes('temporalidade') ? ' min' : '%';
            updateValue(id, suffix);
        });
    };

    radios.forEach((radio) => {
        radio.addEventListener('change', () => {
            applyPreset(radio.value);
        });
    });

    if (!fromOldInput) {
        const checked = document.querySelector('input[name="cenario"]:checked');
        if (checked) {
            applyPreset(checked.value);
        }
    }
})();
</script>
@endsection
