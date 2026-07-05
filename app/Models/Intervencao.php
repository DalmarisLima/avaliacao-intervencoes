<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intervencao extends Model
{
    use HasFactory;

    /**
     * Nome explícito da tabela porque o pluralizador padrão
     * gera "intervencaos" para a classe "Intervencao".
     */
    protected $table = 'intervencoes';

    protected $fillable = [
        'user_id',
        'titulo',
        'tipo_atividade',
        'descricao',
        'turma_id',
        'turma',
        'cenario',
        'data_inicio',
        'data_fim',
        'link',
        'limiar_aderencia',
        'limiar_temporalidade_inicio',
        'limiar_temporalidade_fim',
        'limiar_desempenho',
        'dados_gerados_at',
    ];

    protected function casts(): array
    {
        return [
            'dados_gerados_at' => 'datetime',
            'data_inicio' => 'date',
            'data_fim' => 'date',
        ];
    }

    /**
     * Acessor para permitir usar $model->tipo (views/forms ainda enviam 'tipo')
     * e mapear para a coluna 'tipo_atividade'.
     */
    public function getTipoAttribute(): ?string
    {
        return $this->attributes['tipo_atividade'] ?? null;
    }

    /**
     * Mutator para quando a aplicação atribui 'tipo', salvar em 'tipo_atividade'.
     */
    public function setTipoAttribute($value): void
    {
        $this->attributes['tipo_atividade'] = $value;
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(IntervencaoArquivo::class, 'intervencao_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turmaRelacao(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function getStatusAttribute(): string
    {
        $hoje = Carbon::today();

        if ($hoje->lt($this->data_inicio)) {
            return 'Novo';
        }

        if ($hoje->between($this->data_inicio, $this->data_fim)) {
            return 'Em andamento';
        }

        return 'Finalizado';
    }
}
