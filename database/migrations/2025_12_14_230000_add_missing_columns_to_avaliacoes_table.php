<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            // Apenas adicione as colunas se elas não existirem (SQLite tolera)
            if (!Schema::hasColumn('avaliacoes', 'cenario')) {
                $table->string('cenario')->nullable()->after('intervencao_id');
            }

            if (!Schema::hasColumn('avaliacoes', 'adesao')) {
                $table->unsignedTinyInteger('adesao')->default(0)->after('cenario');
            }

            if (!Schema::hasColumn('avaliacoes', 'aderencia')) {
                $table->unsignedTinyInteger('aderencia')->default(0)->after('adesao');
            }

            if (!Schema::hasColumn('avaliacoes', 'temporalidade')) {
                $table->unsignedTinyInteger('temporalidade')->default(0)->after('aderencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            if (Schema::hasColumn('avaliacoes', 'temporalidade')) {
                $table->dropColumn('temporalidade');
            }
            if (Schema::hasColumn('avaliacoes', 'aderencia')) {
                $table->dropColumn('aderencia');
            }
            if (Schema::hasColumn('avaliacoes', 'adesao')) {
                $table->dropColumn('adesao');
            }
            if (Schema::hasColumn('avaliacoes', 'cenario')) {
                $table->dropColumn('cenario');
            }
        });
    }
};
