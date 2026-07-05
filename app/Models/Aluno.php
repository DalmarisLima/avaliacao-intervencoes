<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aluno extends Model
{
    protected $fillable = [
        'turma_id',
        'numero',
        'nome',
    ];

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }
}
