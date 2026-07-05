<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intervencao_id')
                ->constrained('intervencoes')
                ->onDelete('cascade');

            $table->integer('adesao');          // %
            $table->integer('aderencia');       // %
            $table->integer('temporalidade');   // %
            $table->integer('desempenho');       // %

            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};