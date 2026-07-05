@props([
    'status',
    'processado' => false,
])

@php
    $st = \Illuminate\Support\Str::lower((string) $status);
    $badgeClass = match (true) {
        str_contains($st, 'novo') => 'bg-secondary',
        str_contains($st, 'andamento') => 'bg-warning text-dark',
        str_contains($st, 'finaliz') => 'bg-success',
        default => 'bg-secondary',
    };
@endphp

<span class="badge {{ $badgeClass }}">{{ $status }}</span>
@if ($processado)
    <span class="badge bg-primary ms-1" title="Dados sintéticos gerados">Processado</span>
@else
    <span class="badge bg-light text-dark border ms-1" title="Aguardando definição de cenário">Pendente</span>
@endif
