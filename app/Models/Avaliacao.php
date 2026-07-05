<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    /**
     * Nome explícito da tabela para evitar pluralização incorreta
     * (Eloquent pode gerar "avaliacaos" para a classe "Avaliacao").
     */
    protected $table = 'avaliacoes';

    protected $fillable = [
        'intervencao_id',
        'cenario',
        'aluno_numero',
        'aluno_nome',
        'tipo',
        'adesao',
        'aderencia',
        'temporalidade',
        'temporalidade_inicio',
        'temporalidade_fim',
        'desempenho',
        'observacoes',
    ];

    /**
     * Cast adesao to boolean so model consumers see true/false.
     */
    protected $casts = [
        'adesao' => 'boolean',
    ];

    public function intervencao()
    {
        return $this->belongsTo(Intervencao::class);
    }

    /**
     * Setter para adesão: aceita 'sim'/'nao' ou 1/0
     */
    public function setAdesaoAttribute($value)
    {
        // Aceita 'sim'/'nao' vindos do formulário ou 1/0 já convertidos.
        if (is_string($value)) {
            $value = strtolower($value) === 'sim' ? 1 : 0;
        }

        $this->attributes['adesao'] = $value;
    }
}
