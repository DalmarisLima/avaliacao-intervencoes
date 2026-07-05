<?php

namespace Tests\Unit;

use App\Support\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_remove_tags_perigosos(): void
    {
        $html = '<p>Olá <strong>mundo</strong><script>alert(1)</script></p>';
        $clean = RichTextSanitizer::clean($html);

        $this->assertStringContainsString('<strong>mundo</strong>', $clean);
        $this->assertStringNotContainsString('script', $clean);
    }

    public function test_plain_remove_formatacao(): void
    {
        $this->assertSame('Texto simples', RichTextSanitizer::plain('<p>Texto <em>simples</em></p>'));
    }

    public function test_permite_link_mailto(): void
    {
        $html = '<p>Contato: <a href="mailto:pesquisa@uf.br">pesquisa@uf.br</a></p>';
        $clean = RichTextSanitizer::clean($html);

        $this->assertStringContainsString('href="mailto:pesquisa@uf.br"', $clean);
        $this->assertStringContainsString('pesquisa@uf.br', $clean);
    }

    public function test_rejeita_javascript_em_link(): void
    {
        $html = '<p><a href="javascript:alert(1)">x</a></p>';
        $clean = RichTextSanitizer::clean($html);

        $this->assertStringNotContainsString('javascript', $clean);
        $this->assertStringNotContainsString('<a ', $clean);
    }

    public function test_normalize_href_converte_email(): void
    {
        $this->assertSame('mailto:docente@email.com', RichTextSanitizer::normalizeHref('docente@email.com'));
    }
}
