<?php

namespace Tests\Feature;

use App\Services\DatabaseSchemaEnsurer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaEnsurerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_cria_colunas_do_estudo_quando_ausentes(): void
    {
        if (! Schema::hasTable('estudo_configuracao')) {
            $this->markTestSkipped('Tabela estudo_configuracao ausente.');
        }

        Schema::table('estudo_configuracao', function ($table) {
            if (Schema::hasColumn('estudo_configuracao', 'conteudos_intervencao')) {
                $table->dropColumn('conteudos_intervencao');
            }
            if (Schema::hasColumn('estudo_configuracao', 'conteudo_intervencao')) {
                $table->dropColumn('conteudo_intervencao');
            }
            if (Schema::hasColumn('estudo_configuracao', 'dados_orientador')) {
                $table->dropColumn('dados_orientador');
            }
        });

        app(DatabaseSchemaEnsurer::class)->ensure();

        $this->assertTrue(Schema::hasColumn('estudo_configuracao', 'dados_orientador'));
        $this->assertTrue(Schema::hasColumn('estudo_configuracao', 'conteudo_intervencao'));
        $this->assertTrue(Schema::hasColumn('estudo_configuracao', 'conteudos_intervencao'));
    }
}
