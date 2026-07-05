<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero');
            $table->string('nome');
            $table->timestamps();

            $table->unique(['turma_id', 'numero']);
        });

        Schema::table('intervencoes', function (Blueprint $table) {
            $table->foreignId('turma_id')->nullable()->after('descricao')->constrained('turmas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intervencoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('turma_id');
        });

        Schema::dropIfExists('alunos');
        Schema::dropIfExists('turmas');
    }
};
