<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntervencaoArquivo extends Model
{
        use HasFactory;

    protected $fillable = [
        'intervencao_id',
        'nome_original',
        'caminho',
        'mime_type',
        'tamanho',
    ];

    public function intervencao()
    {
        return $this->belongsTo(Intervencao::class);
    }

}
