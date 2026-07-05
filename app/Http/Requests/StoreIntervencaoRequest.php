<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntervencaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['required', 'string'],
            'turma' => ['required', 'string', 'in:'.implode(',', config('turmas.opcoes', []))],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'link' => ['nullable', 'string', 'max:2048'],
            'arquivos.*' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
