@props([
    'current' => 1,
])

@php
    $steps = [
        1 => ['label' => 'Intervenção', 'hint' => 'Descrição da ação pedagógica'],
        2 => ['label' => 'Cenário', 'hint' => 'Limiares e perfil'],
        3 => ['label' => 'Resultados', 'hint' => 'Análise gerada'],
    ];
@endphp

<nav class="wizard-steps" aria-label="Etapas do cadastro da intervenção">
    @foreach ($steps as $num => $step)
        @php
            $state = $num < $current ? 'is-done' : ($num === $current ? 'is-active' : '');
        @endphp
        <div class="wizard-step {{ $state }}">
            <div class="wizard-step__circle" aria-hidden="true">
                @if ($num < $current)
                    ✓
                @else
                    {{ $num }}
                @endif
            </div>
            <span class="wizard-step__label">{{ $step['label'] }}</span>
            <span class="wizard-step__hint">{{ $step['hint'] }}</span>
        </div>
    @endforeach
</nav>
