<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MetodologiaController extends Controller
{
    public function download(): StreamedResponse
    {
        $path = base_path('docs/metodologia-avaliacao-eficacia.md');

        abort_unless(is_readable($path), 404, 'Documento não encontrado.');

        $filename = 'metodologia-avaliacao-eficacia-intervencoes.md';

        return response()->streamDownload(
            static function () use ($path): void {
                echo file_get_contents($path);
            },
            $filename,
            [
                'Content-Type' => 'text/markdown; charset=UTF-8',
            ]
        );
    }

    public function view(): Response
    {
        $path = base_path('docs/metodologia-avaliacao-eficacia.md');

        abort_unless(is_readable($path), 404);

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
