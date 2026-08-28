<?php

namespace Tests\Unit;

use App\Services\ExecutiveSummaryFormatter;
use PHPUnit\Framework\TestCase;

class ExecutiveSummaryFormatterTest extends TestCase
{
    public function test_it_formats_tables_and_removes_unanswered_content_and_markdown_artifacts(): void
    {
        $summary = <<<'MD'
1. Perfil del negocio
| Ítem | Información |
| --- | --- |
| **Ubicación** | *Dato no proporcionado* |
| **Situación** | Empresa en crecimiento |
---
2
2. Marketing actual
- **Canales:** No se especificó.
3. Objetivos
- **Aumentar ventas** mediante asesoría personalizada.
MD;

        $sections = (new ExecutiveSummaryFormatter)->sections($summary);

        $this->assertCount(2, $sections);
        $this->assertSame('Perfil del negocio', $sections[0]['titulo']);
        $this->assertStringContainsString('<table>', $sections[0]['html']);
        $this->assertStringContainsString('<strong>Situación</strong>', $sections[0]['html']);
        $this->assertStringNotContainsString('Dato no proporcionado', $sections[0]['html']);
        $this->assertSame('Objetivos', $sections[1]['titulo']);
        $this->assertStringNotContainsString('**', $sections[1]['html']);
        $this->assertStringNotContainsString('|', $sections[1]['html']);
    }

    public function test_it_removes_legacy_next_step_messages(): void
    {
        $summary = <<<'MD'
1. Objetivos
- Conseguir mas clientes y vender mas.

*Proximo paso:* recopilar la informacion faltante listada en la seccion 11.
MD;

        $sections = (new ExecutiveSummaryFormatter)->sections($summary);

        $this->assertCount(1, $sections);
        $this->assertStringContainsString('Conseguir mas clientes', $sections[0]['html']);
        $this->assertStringNotContainsString('Proximo paso', $sections[0]['html']);
        $this->assertStringNotContainsString('seccion 11', $sections[0]['html']);
    }

    public function test_it_converts_the_visual_editor_content_back_to_clean_markdown(): void
    {
        $formatter = new ExecutiveSummaryFormatter;
        $html = '<p>Una <strong>estrategia clara</strong>.</p>'
            .'<ul><li>Primer objetivo</li><li>Segundo objetivo</li></ul>'
            .'<table><thead><tr><th>Canal</th><th>Uso</th></tr></thead><tbody><tr><td>Instagram</td><td>Ventas</td></tr></tbody></table>'
            .'<script>alert("xss")</script>';

        $markdown = $formatter->markdownFromHtml($html);

        $this->assertStringContainsString('**estrategia clara**', $markdown);
        $this->assertStringContainsString('- Primer objetivo', $markdown);
        $this->assertStringContainsString('| Canal | Uso |', $markdown);
        $this->assertStringContainsString('| Instagram | Ventas |', $markdown);
        $this->assertStringNotContainsString('script', $markdown);
        $this->assertStringNotContainsString('alert', $markdown);
    }

    public function test_it_separates_numbered_items_that_arrive_on_the_same_line(): void
    {
        $sections = (new ExecutiveSummaryFormatter)->sections(<<<'MD'
## 1 Calendario semanal
1. **Lunes** – Infografía. 2. **Miércoles** – Carrusel. 3. **Viernes** – Fotografía.
MD);

        $this->assertStringContainsString('<ol>', $sections[0]['html']);
        $this->assertSame(3, substr_count($sections[0]['html'], '<li>'));
        $this->assertStringContainsString('<strong>Miércoles</strong>', $sections[0]['html']);
    }
}
