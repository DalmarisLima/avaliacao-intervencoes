<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intervencoes', function (Blueprint $table) {
            $table->id();

            $table->string('titulo');
            $table->string('tipo_atividade'); // Presencial | Online
            $table->text('descricao');

            $table->date('data_inicio');
            $table->date('data_fim');

            $table->string('link')->nullable();

            $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervencoes');
    }
};
