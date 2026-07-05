<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Intervencao;
use Illuminate\Support\Facades\DB;

class TurmaController extends Controller
{
    public function index()
    {
        $userId = (int) auth()->id();

        // Estatísticas agregadas por turma (médias das intervenções baseadas em avaliações PÓS)
        $stats = DB::table('intervencoes')
            ->join('avaliacoes', 'intervencoes.id', '=', 'avaliacoes.intervencao_id')
            ->selectRaw(
                'intervencoes.turma as turma,
                AVG(avaliacoes.adesao) as adesao_avg,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.aderencia ELSE 0 END) as aderencia_avg,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.temporalidade_inicio ELSE 0 END) as temporalidade_inicio_avg,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.temporalidade_fim ELSE 0 END) as temporalidade_fim_avg,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN (avaliacoes.temporalidade_inicio + avaliacoes.temporalidade_fim) / 2 ELSE 0 END) as temporalidade_avg,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.desempenho ELSE 0 END) as desempenho_avg'
            )
            ->where('avaliacoes.tipo', 'pos')
            ->where('intervencoes.user_id', $userId)
            ->whereNotNull('intervencoes.turma')
            ->groupBy('intervencoes.turma')
            ->get();

        // Também queremos listar turmas mesmo sem avaliações (apenas das intervenções)
        $turmas = Intervencao::select('turma')
            ->where('user_id', $userId)
            ->whereNotNull('turma')
            ->distinct()
            ->pluck('turma');

        // Transformar stats em array indexado por turma para fácil lookup
        $statsByTurma = [];
        foreach ($stats as $s) {
            $statsByTurma[$s->turma] = $s;
        }

        // Mapear intervenções por turma (títulos) e contagens
        $intervencoes = Intervencao::select('turma', 'titulo')
            ->where('user_id', $userId)
            ->whereNotNull('turma')
            ->orderBy('turma')
            ->orderBy('id')
            ->get()
            ->groupBy('turma')
            ->map(function ($group) {
                return $group->pluck('titulo')->toArray();
            })
            ->toArray();

        $intervCounts = [];
        foreach ($intervencoes as $t => $list) {
            $intervCounts[$t] = count($list);
        }

        return view('turmas.index', compact('turmas', 'statsByTurma', 'intervencoes', 'intervCounts'));
    }
}
