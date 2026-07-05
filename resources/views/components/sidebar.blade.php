<aside class="sidebar">
    <div class="sidebar__brand">
        <span class="sidebar__app-name">Intervenções</span>
        <span class="sidebar__app-meta">Avaliação pedagógica</span>
    </div>

    @auth
    <nav class="sidebar__nav" aria-label="Menu principal">
        <a href="{{ route('home') }}" class="sidebar__link {{ request()->routeIs('home') || request()->routeIs('intervencoes.*') ? 'active' : '' }}">Intervenções</a>
        <a href="{{ route('turmas.index') }}" class="sidebar__link {{ request()->routeIs('turmas.*') ? 'active' : '' }}">Turmas</a>
        <a href="{{ route('resultados') }}" class="sidebar__link {{ request()->routeIs('resultados') ? 'active' : '' }}">Resultados</a>
    </nav>

    <div class="sidebar__footer">
        <span class="sidebar__user-name">{{ auth()->user()->name }}</span>
        <span class="sidebar__user-email">{{ auth()->user()->email }}</span>
        <form method="POST" action="{{ route('logout') }}" class="sidebar__logout">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Sair</button>
        </form>
    </div>
    @else
    <nav class="sidebar__nav">
        <span class="sidebar__link text-muted" style="pointer-events: none;">Faça login para continuar</span>
    </nav>
    @endauth
</aside>
