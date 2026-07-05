<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudo_configuracao', function (Blueprint $table) {
            $table->text('dados_orientador')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('estudo_configuracao', function (Blueprint $table) {
            $table->dropColumn('dados_orientador');
        });
    }
};
