<?php

use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\IntervencaoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MetodologiaController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\TurmaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');
    Route::redirect('/acesso', '/login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [IntervencaoController::class, 'index'])->name('home');

    Route::get('/intervencoes', [IntervencaoController::class, 'index'])
        ->name('intervencoes.index');

    Route::get('/intervencoes/nova', [IntervencaoController::class, 'create'])
        ->name('intervencoes.create');

    Route::post('/intervencoes/iniciar-cenario', [IntervencaoController::class, 'iniciarCenario'])
        ->name('intervencoes.iniciar-cenario');

    Route::get('/intervencoes/{intervencao}/cenario', [IntervencaoController::class, 'definirCenario'])
        ->name('intervencoes.definir-cenario');

    Route::post('/intervencoes/{intervencao}/cenario', [IntervencaoController::class, 'salvarCenario'])
        ->name('intervencoes.salvar-cenario');

    Route::get('/intervencoes/{intervencao}/avaliacao', [AvaliacaoController::class, 'create'])
        ->name('avaliacoes.create');

    Route::post('/intervencoes/{intervencao}/avaliacao', [AvaliacaoController::class, 'store'])
        ->name('avaliacoes.store');

    Route::get('/turmas', [TurmaController::class, 'index'])->name('turmas.index');

    Route::get('/resultados', [ResultadosController::class, 'index'])->name('resultados');

    Route::get('/docs/metodologia-eficacia', [MetodologiaController::class, 'download'])
        ->name('docs.metodologia-eficacia');
});
