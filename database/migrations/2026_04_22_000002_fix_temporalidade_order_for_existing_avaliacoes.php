<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('avaliacoes')
            ->where('adesao', 1)
            ->whereColumn('temporalidade_fim', '<=', 'temporalidade_inicio')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $inicio = (int) ($row->temporalidade_inicio ?? 0);
                    $fim = min(240, $inicio + 15);

                    DB::table('avaliacoes')
                        ->where('id', $row->id)
                        ->update([
                            'temporalidade_fim' => $fim,
                            'temporalidade' => (int) round(($inicio + $fim) / 2),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Migração de saneamento de dados sem rollback seguro.
    }
};