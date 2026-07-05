<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('avaliacoes', 'temporalidade_inicio')) {
                $table->unsignedTinyInteger('temporalidade_inicio')->default(0)->after('temporalidade');
            }

            if (!Schema::hasColumn('avaliacoes', 'temporalidade_fim')) {
                $table->unsignedTinyInteger('temporalidade_fim')->default(0)->after('temporalidade_inicio');
            }
        });

        if (Schema::hasColumn('avaliacoes', 'temporalidade')) {
            DB::table('avaliacoes')->update([
                'temporalidade_inicio' => DB::raw('COALESCE(temporalidade, 0)'),
                'temporalidade_fim' => DB::raw('COALESCE(temporalidade, 0)'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            if (Schema::hasColumn('avaliacoes', 'temporalidade_fim')) {
                $table->dropColumn('temporalidade_fim');
            }

            if (Schema::hasColumn('avaliacoes', 'temporalidade_inicio')) {
                $table->dropColumn('temporalidade_inicio');
            }
        });
    }
};