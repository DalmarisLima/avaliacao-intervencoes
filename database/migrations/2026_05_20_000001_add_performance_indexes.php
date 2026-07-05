<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->index(['intervencao_id', 'tipo'], 'avaliacoes_intervencao_tipo_index');
            $table->index(['intervencao_id', 'aluno_numero', 'tipo'], 'avaliacoes_intervencao_aluno_tipo_index');
        });

        Schema::table('intervencoes', function (Blueprint $table) {
            $table->index(['user_id', 'turma'], 'intervencoes_user_turma_index');
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropIndex('avaliacoes_intervencao_tipo_index');
            $table->dropIndex('avaliacoes_intervencao_aluno_tipo_index');
        });

        Schema::table('intervencoes', function (Blueprint $table) {
            $table->dropIndex('intervencoes_user_turma_index');
        });
    }
};
