<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DatabaseSchemaEnsurer
{
    public function ensure(): void
    {
        try {
            $this->ensureEstudoConfiguracaoColumns();
        } catch (Throwable $exception) {
            Log::error('Falha ao garantir schema do banco.', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function ensureEstudoConfiguracaoColumns(): void
    {
        if (! Schema::hasTable('estudo_configuracao')) {
            return;
        }

        if (! Schema::hasColumn('estudo_configuracao', 'dados_orientador')) {
            Schema::table('estudo_configuracao', function (Blueprint $table) {
                $table->text('dados_orientador')->nullable();
            });
        }

        if (! Schema::hasColumn('estudo_configuracao', 'conteudo_intervencao')) {
            Schema::table('estudo_configuracao', function (Blueprint $table) {
                $table->text('conteudo_intervencao')->nullable();
            });
        }

        if (! Schema::hasColumn('estudo_configuracao', 'conteudos_intervencao')) {
            Schema::table('estudo_configuracao', function (Blueprint $table) {
                $table->json('conteudos_intervencao')->nullable();
            });
        }
    }
}
