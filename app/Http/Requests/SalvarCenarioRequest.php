<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalvarCenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $intervencao = $this->route('intervencao');

        return $intervencao && (int) $intervencao->user_id === (int) $this->user()?->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cenario' => ['required', 'string', 'in:flexivel,dificil,difícil,leve,personalizado'],
            'limiar_aderencia' => ['required', 'integer', 'min:0', 'max:100'],
            'limiar_temporalidade_inicio' => ['required', 'integer', 'min:0', 'max:240'],
            'limiar_temporalidade_fim' => ['required', 'integer', 'min:0', 'max:240'],
            'limiar_desempenho' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
