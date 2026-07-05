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
        Schema::create('intervencao_arquivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intervencao_id')
            ->constrained('intervencoes')
            ->onDelete('cascade');

            $table->string('nome_original');
            $table->string('caminho');
            $table->string('mime_type')->nullable();
            $table->integer('tamanho')->nullable();

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervencao_arquivos');
    }
};
