<?php

namespace Tests\Unit;

use App\Services\CampaignAudienceService;
use PHPUnit\Framework\TestCase;

class CampaignAudienceServiceTest extends TestCase
{
    public function test_it_reads_legacy_json_without_showing_json_keys_to_the_user(): void
    {
        $legacy = json_encode([
            ['tipo_edades' => 'Profesionales de 22 a 42 años', 'descripcion' => 'Buscan desayunos cerca del trabajo.'],
            ['tipo_edades' => 'Equipos de oficina de 25 a 50 años', 'descripcion' => 'Reservan espacios para reuniones breves.'],
        ], JSON_UNESCAPED_UNICODE);

        $segments = (new CampaignAudienceService)->parse($legacy);

        $this->assertCount(2, $segments);
        $this->assertSame('Profesionales de 22 a 42 años', $segments[0]['tipo_edades']);
        $this->assertSame('Buscan desayunos cerca del trabajo.', $segments[0]['descripcion']);
        $this->assertSame('Equipos de oficina de 25 a 50 años', $segments[1]['tipo_edades']);
    }

    public function test_it_keeps_two_seeded_audiences_separate_after_serializing(): void
    {
        $service = new CampaignAudienceService;
        $segments = [
            ['tipo_edades' => 'Profesionales de 22 a 42 años', 'descripcion' => 'Buscan desayunos cerca del trabajo.'],
            ['tipo_edades' => 'Equipos de oficina de 25 a 50 años', 'descripcion' => 'Reservan espacios para reuniones breves.'],
        ];

        $this->assertSame($segments, $service->parse($service->serialize($segments)));
    }

    public function test_it_separates_a_flat_audience_table_into_essential_segments(): void
    {
        $source = 'Segmento Necesidad principal Motivación Objeción probable Contenido / Canal recomendado '
            .'Adultos (35‑55 a) – “Planificador” Entender aportes y calcular pensión. Seguridad financiera familiar. '
            .'Padres de familia (30‑45 a) – “Protector” Garantizar el futuro de la familia. Tranquilidad para sus hijos. '
            .'Profesionales independientes (30‑50 a) – “Emprendedor” Optimizar aportes y evitar errores.';

        $segments = (new CampaignAudienceService)->parse($source);

        $this->assertCount(3, $segments);
        $this->assertSame('Adultos (35‑55 a)', $segments[0]['tipo_edades']);
        $this->assertStringContainsString('Planificador', $segments[0]['descripcion']);
        $this->assertSame('Padres de familia (30‑45 a)', $segments[1]['tipo_edades']);
        $this->assertSame('Profesionales independientes (30‑50 a)', $segments[2]['tipo_edades']);
    }

    public function test_it_separates_segments_with_typographic_hyphens_and_years(): void
    {
        $source = 'Adultos planificadores (35‑55 años): Buscan estabilidad financiera para su familia. '
            .'Padres protectores (30‑45 años): Quieren garantizar un futuro tranquilo para sus hijos. '
            .'Profesionales independientes (30–50 años): Desean optimizar sus aportes. '
            .'Empresas PYME (25—55 años): Buscan mejorar la retención y el bienestar.';

        $segments = (new CampaignAudienceService)->parse($source);

        $this->assertCount(4, $segments);
        $this->assertSame('Adultos planificadores (35‑55 años)', $segments[0]['tipo_edades']);
        $this->assertSame('Padres protectores (30‑45 años)', $segments[1]['tipo_edades']);
        $this->assertSame('Profesionales independientes (30–50 años)', $segments[2]['tipo_edades']);
        $this->assertSame('Empresas PYME (25—55 años)', $segments[3]['tipo_edades']);
    }
}
