<?php

use App\Http\Controllers\ResultadosController;
use Illuminate\Support\Facades\Route;

/*
| Rotas JSON autenticadas por sessão (mesmo domínio do app Blade).
| Prefixo /api mantido para compatibilidade com fetch() nas views.
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/turma/{turma}', [ResultadosController::class, 'getTurmaStats'])
        ->name('api.turma-stats');

    Route::get('/turma/{turma}/alunos', [ResultadosController::class, 'getAlunosByTurma'])
        ->name('api.turma-alunos');

    Route::get('/turma/{turma}/intervencoes', [ResultadosController::class, 'getIntervencoesByTurma'])
        ->name('api.turma-intervencoes');

    Route::get('/turma/{turma}/interpretacao', [ResultadosController::class, 'getInterpretacaoTurma'])
        ->name('api.turma-interpretacao');

    Route::get('/turma/{turma}/intervencao/{intervencao}/analise', [ResultadosController::class, 'getIntervencaoAnalise'])
        ->name('api.intervencao-analise');
});
