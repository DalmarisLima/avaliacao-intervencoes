<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intervencoes', function (Blueprint $table) {
            $table->timestamp('dados_gerados_at')->nullable()->after('limiar_desempenho');
        });

        DB::table('intervencoes')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('avaliacoes')
                    ->whereColumn('avaliacoes.intervencao_id', 'intervencoes.id')
                    ->where('tipo', 'pos');
            })
            ->update(['dados_gerados_at' => now()]);

        $duplicatas = DB::table('avaliacoes')
            ->select('intervencao_id', 'aluno_numero', 'tipo', DB::raw('MIN(id) as keep_id'))
            ->groupBy('intervencao_id', 'aluno_numero', 'tipo')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicatas as $row) {
            DB::table('avaliacoes')
                ->where('intervencao_id', $row->intervencao_id)
                ->where('aluno_numero', $row->aluno_numero)
                ->where('tipo', $row->tipo)
                ->where('id', '!=', $row->keep_id)
                ->delete();
        }

        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->unique(
                ['intervencao_id', 'aluno_numero', 'tipo'],
                'avaliacoes_intervencao_aluno_tipo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropUnique('avaliacoes_intervencao_aluno_tipo_unique');
        });

        Schema::table('intervencoes', function (Blueprint $table) {
            $table->dropColumn('dados_gerados_at');
        });
    }
};
