<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intervencoes', function (Blueprint $table) {
            // Limiares de avaliação do cenário (valores configuráveis pelo professor)
            $table->unsignedTinyInteger('limiar_aderencia')->default(25)->comment('Limiar mínimo de aderência (0-100)');
            $table->unsignedTinyInteger('limiar_temporalidade_inicio')->default(20)->comment('Limiar máximo de temporalidade início (0-240)');
            $table->unsignedTinyInteger('limiar_temporalidade_fim')->default(60)->comment('Limiar máximo de temporalidade fim (0-240)');
            $table->unsignedTinyInteger('limiar_desempenho')->default(25)->comment('Limiar mínimo de desempenho (0-100)');
        });
    }

    public function down(): void
    {
        Schema::table('intervencoes', function (Blueprint $table) {
            $table->dropColumn(['limiar_aderencia', 'limiar_temporalidade_inicio', 'limiar_temporalidade_fim', 'limiar_desempenho']);
        });
    }
};
