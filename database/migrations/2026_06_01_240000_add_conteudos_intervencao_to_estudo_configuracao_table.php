<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estudo_configuracao', function (Blueprint $table) {
            if (! Schema::hasColumn('estudo_configuracao', 'conteudos_intervencao')) {
                $table->json('conteudos_intervencao')->nullable();
            }
        });

        if (! Schema::hasColumn('estudo_configuracao', 'conteudo_intervencao')) {
            return;
        }

        DB::table('estudo_configuracao')
            ->whereNotNull('conteudo_intervencao')
            ->where('conteudo_intervencao', '!=', '')
            ->where(function ($query) {
                $query->whereNull('conteudos_intervencao')
                    ->orWhere('conteudos_intervencao', '');
            })
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('estudo_configuracao')
                    ->where('id', $row->id)
                    ->update([
                        'conteudos_intervencao' => json_encode([
                            'flexivel' => $row->conteudo_intervencao,
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('estudo_configuracao', function (Blueprint $table) {
            if (Schema::hasColumn('estudo_configuracao', 'conteudos_intervencao')) {
                $table->dropColumn('conteudos_intervencao');
            }
        });
    }
};
