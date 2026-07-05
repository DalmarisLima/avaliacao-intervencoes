<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            // Adicionar coluna de aluno (identificador único dentro de uma intervenção)
            $table->integer('aluno_numero')->default(1)->after('intervencao_id');
            // Nome do aluno para exibição
            $table->string('aluno_nome')->nullable()->after('aluno_numero');
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropColumn(['aluno_numero', 'aluno_nome']);
        });
    }
};
