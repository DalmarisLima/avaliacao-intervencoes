<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turma extends Model
{
    protected $fillable = [
        'nome',
        'slug',
    ];

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class);
    }

    public function intervencoes(): HasMany
    {
        return $this->hasMany(Intervencao::class);
    }
}
