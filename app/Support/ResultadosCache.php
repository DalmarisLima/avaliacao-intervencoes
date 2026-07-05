<?php

namespace App\Support;

use App\Models\Intervencao;
use Illuminate\Support\Facades\Cache;

class ResultadosCache
{
    public static function indexKey(int $userId): string
    {
        return "resultados.index.{$userId}";
    }

    public static function turmaKey(int $userId, string $turma, string $suffix): string
    {
        $hash = md5($turma);

        return "resultados.v2.{$suffix}.{$userId}.{$hash}";
    }

    public static function forgetUser(int $userId): void
    {
        Cache::forget(self::indexKey($userId));

        $turmas = Intervencao::query()
            ->where('user_id', $userId)
            ->whereNotNull('turma')
            ->distinct()
            ->pluck('turma')
            ->merge(config('turmas.opcoes', []))
            ->unique()
            ->filter();

        $intervencaoIds = Intervencao::query()
            ->where('user_id', $userId)
            ->pluck('id');

        foreach ($turmas as $turma) {
            Cache::forget(self::turmaKey($userId, (string) $turma, 'stats'));
            Cache::forget(self::turmaKey($userId, (string) $turma, 'alunos'));
            Cache::forget(self::turmaKey($userId, (string) $turma, 'intervencoes'));

            foreach ($intervencaoIds as $intervencaoId) {
                Cache::forget(self::turmaKey($userId, (string) $turma, 'stats_i'.$intervencaoId));
            }
        }
    }
}
