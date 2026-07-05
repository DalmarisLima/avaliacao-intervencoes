<?php

namespace App\Services;

use App\Enums\Cenario;
use App\Models\EstudoConfiguracao;
use App\Models\Intervencao;
use App\Models\Turma;
use App\Models\User;
use App\Support\RichTextSanitizer;
use Illuminate\Support\Str;

class IntervencaoSetupService
{
    public function criarParaUsuario(User $user, ?Cenario $cenario = null): Intervencao
    {
        $estudo = EstudoConfiguracao::obter();
        $turmaNome = (string) config('intervencao.turma_padrao', '2º Ano A');
        $turmaId = Turma::query()->where('nome', $turmaNome)->value('id');
        $cenarioAlvo = $cenario ?? Cenario::Flexivel;

        $limiares = $cenarioAlvo->limiaresPadrao();
        $conteudo = RichTextSanitizer::clean($estudo->conteudoIntervencaoPara($cenarioAlvo) ?? '');
        $tituloBase = (string) config('intervencao.intervencao_titulo_padrao', 'Intervenção pedagógica');
        $titulo = Str::limit($tituloBase.' — '.$cenarioAlvo->rotulo(), 255, '');

        return Intervencao::create([
            'user_id' => $user->id,
            'titulo' => $titulo,
            'tipo_atividade' => 'Presencial',
            'descricao' => Str::limit(strip_tags($conteudo), 500, '') ?: 'Intervenção pedagógica simulada.',
            'turma_id' => $turmaId,
            'turma' => $turmaNome,
            'cenario' => $cenarioAlvo->value,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addWeek()->toDateString(),
            'limiar_aderencia' => $limiares['aderencia'],
            'limiar_temporalidade_inicio' => $limiares['temporalidade_inicio'],
            'limiar_temporalidade_fim' => $limiares['temporalidade_fim'],
            'limiar_desempenho' => $limiares['desempenho'],
        ]);
    }
}
