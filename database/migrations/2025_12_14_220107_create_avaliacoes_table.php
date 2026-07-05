<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Evita tentar criar a tabela se ela já existir (em ambientes onde
        // uma versão anterior da migration já criou a tabela)
        if (!Schema::hasTable('avaliacoes')) {
            Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervencao_id')->constrained()->onDelete('cascade');
            $table->string('cenario');
            $table->unsignedTinyInteger('adesao');
            $table->unsignedTinyInteger('aderencia');
            $table->unsignedTinyInteger('temporalidade');
            $table->unsignedTinyInteger('desempenho');
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
