<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvaliacaoRequest extends FormRequest
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
            'cenario' => ['required', 'string', 'in:flexivel,moderado,dificil,difícil,personalizado,leve,rigido,Rígido,modelado,modelo'],
            'adesao' => ['required', 'in:sim,nao'],
            'aderencia' => ['required', 'integer', 'min:0', 'max:100'],
            'temporalidade_inicio' => ['required', 'integer', 'min:0', 'max:240'],
            'temporalidade_fim' => ['required', 'integer', 'min:0', 'max:240'],
            'desempenho' => ['required', 'integer', 'min:0', 'max:100'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (
                $this->input('adesao') === 'sim'
                && (int) $this->input('temporalidade_fim') <= (int) $this->input('temporalidade_inicio')
            ) {
                $validator->errors()->add(
                    'temporalidade_fim',
                    'Para adesão "Sim", o tempo de finalização deve ser maior que o tempo de início.'
                );
            }
        });
    }
}
