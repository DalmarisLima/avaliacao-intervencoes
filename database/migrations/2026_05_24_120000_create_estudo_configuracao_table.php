<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudo_configuracao', function (Blueprint $table) {
            $table->id();
            $table->string('titulo')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('dados_pesquisador')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudo_configuracao');
    }
};
