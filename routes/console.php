<?php

use App\Services\DatabaseSchemaEnsurer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:reset', function () {
    DB::statement('PRAGMA foreign_keys=OFF');

    DB::table('intervencao_arquivos')->delete();
    DB::table('avaliacoes')->delete();
    DB::table('intervencoes')->delete();
    DB::table('users')->delete();

    DB::statement('PRAGMA foreign_keys=ON');

    $this->info('Dados de usuários, intervenções e avaliações removidos.');
})->purpose('Limpa dados operacionais da aplicação (SQLite).');

Artisan::command('app:ensure-schema', function () {
    app(DatabaseSchemaEnsurer::class)->ensure();

    $ok = \Illuminate\Support\Facades\Schema::hasColumn('estudo_configuracao', 'conteudos_intervencao');

    $ok
        ? $this->info('Schema OK: colunas de estudo_configuracao disponíveis.')
        : $this->error('Colunas de estudo_configuracao ainda não existem. Verifique permissões do SQLite.');
})->purpose('Garante colunas necessárias no SQLite (produção/Dokploy).');
