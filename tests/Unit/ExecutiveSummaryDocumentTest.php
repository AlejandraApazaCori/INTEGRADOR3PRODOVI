<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ReporteController;
use App\Models\Empresa;
use App\Models\User;
use App\Services\ExecutiveSummaryFormatter;
use ReflectionMethod;
use Tests\TestCase;
use ZipArchive;

class ExecutiveSummaryDocumentTest extends TestCase
{
    public function test_google_document_source_contains_header_content_and_formatted_table(): void
    {
        $empresa = new Empresa([
            'nombre_empresa' => 'Empresa de prueba',
            'tipo_empresa' => 'Consultora',
        ]);
        $empresa->setRelation('usuario', new User([
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.com',
        ]));
        $formatter = new ExecutiveSummaryFormatter;
        $sections = $formatter->sections(<<<'MD'
1. Perfil empresarial
| Indicador | Resultado |
| --- | --- |
| Objetivo | Aumentar ventas |
MD);

        $method = new ReflectionMethod(ReporteController::class, 'reportDocx');
        $document = $method->invoke(new ReporteController($formatter), [
            'empresa' => $empresa,
            'secciones' => $sections,
            'resumen' => '',
        ]);

        $path = tempnam(sys_get_temp_dir(), 'executive-doc-test-');
        file_put_contents($path, $document);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $headerXml = (string) $zip->getFromName('word/header1.xml');
        $documentXml = (string) $zip->getFromName('word/document.xml');
        $this->assertStringContainsString('Resumen ejecutivo empresarial', $headerXml);
        $this->assertStringContainsString('w:type="pct"', $headerXml);
        $this->assertStringContainsString('Empresa de prueba', $documentXml);
        $this->assertStringContainsString('Aumentar ventas', $documentXml);
        $this->assertStringContainsString('w:tblLayout w:type="fixed"', $documentXml);
        $this->assertFalse($zip->locateName('word/footer1.xml'));
        $zip->close();
        unlink($path);
    }
}
