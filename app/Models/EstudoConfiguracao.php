<?php

namespace App\Models;

use App\Enums\Cenario;
use Illuminate\Database\Eloquent\Model;

class EstudoConfiguracao extends Model
{
    protected $table = 'estudo_configuracao';

    protected $fillable = [
        'titulo',
        'objetivo',
        'dados_pesquisador',
        'dados_orientador',
        'conteudo_intervencao',
        'conteudos_intervencao',
    ];

    protected function casts(): array
    {
        return [
            'conteudos_intervencao' => 'array',
        ];
    }

    public static function obter(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'titulo' => config('intervencao.app_titulo'),
                'objetivo' => config('intervencao.app_descricao'),
                'dados_pesquisador' => null,
                'dados_orientador' => null,
                'conteudo_intervencao' => config('intervencao.conteudos_intervencao.flexivel'),
                'conteudos_intervencao' => config('intervencao.conteudos_intervencao'),
            ]
        );
    }

    public function conteudoIntervencaoPara(Cenario|string $cenario): ?string
    {
        $slug = $cenario instanceof Cenario
            ? $cenario->value
            : Cenario::fromInput($cenario)->value;

        $conteudos = $this->conteudos_intervencao ?? [];
        $conteudo = $conteudos[$slug] ?? null;

        if (filled($conteudo)) {
            return $conteudo;
        }

        if ($slug === Cenario::Flexivel->value && filled($this->conteudo_intervencao)) {
            return $this->conteudo_intervencao;
        }

        return config('intervencao.conteudos_intervencao.'.$slug);
    }
}
