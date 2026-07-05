@extends('layouts.auth')

@section('title', 'Entrar')

@section('content')
<div class="auth-screen">
    <div class="auth-screen__grid">
        <section class="auth-screen__invite" aria-labelledby="auth-invite-title">
            <p class="auth-screen__eyebrow">Avaliação de intervenções</p>
            <h1 id="auth-invite-title" class="auth-screen__title">{{ config('intervencao.app_titulo') }}</h1>
            <p class="auth-screen__lead">{{ config('intervencao.app_descricao') }}</p>

            <ol class="auth-screen__steps">
                <li class="auth-screen__step">
                    <span class="auth-screen__step-num" aria-hidden="true">1</span>
                    <span class="auth-screen__step-text">Leia a descrição da intervenção pedagógica.</span>
                </li>
                <li class="auth-screen__step">
                    <span class="auth-screen__step-num" aria-hidden="true">2</span>
                    <span class="auth-screen__step-text">Configure os limiares do cenário de avaliação.</span>
                </li>
                <li class="auth-screen__step">
                    <span class="auth-screen__step-num" aria-hidden="true">3</span>
                    <span class="auth-screen__step-text">Analise os resultados simulados por turma.</span>
                </li>
            </ol>
        </section>

        <section class="auth-screen__panel" aria-labelledby="auth-form-title">
            <div class="auth-screen__card surface-card">
                <div class="auth-screen__card-header">
                    <h2 id="auth-form-title" class="auth-screen__card-title">Entrar</h2>
                    <p class="auth-screen__card-subtitle">Use seu e-mail e senha para acessar a ferramenta.</p>
                </div>

                <form method="POST" action="{{ route('login.attempt') }}" class="auth-form">
                    @csrf

                    <div class="auth-form__field">
                        <label for="email" class="form-label auth-form__label">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control auth-form__input @error('email') is-invalid @enderror"
                            placeholder="professor@example.com"
                            autocomplete="email"
                            inputmode="email"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-form__field">
                        <label for="password" class="form-label auth-form__label">Senha</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control auth-form__input @error('password') is-invalid @enderror"
                            autocomplete="current-password"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary auth-form__submit w-100">
                        Entrar
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
