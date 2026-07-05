@if ($paginator->hasPages())
<div class="pagination-clean">

    <div class="pagination-info">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
        de {{ $paginator->total() }} resultados
    </div>

    <div class="pagination-arrows">
        @if ($paginator->onFirstPage())
            <span class="arrow disabled">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="arrow">←</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="arrow">→</a>
        @else
            <span class="arrow disabled">→</span>
        @endif
    </div>

</div>
@endif
