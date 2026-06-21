<?php

namespace Database\Seeders;

use App\Models\Caracteristica;
use App\Models\Plan;
use App\Models\PlanCaracteristica;
use Illuminate\Database\Seeder;

class PlanesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Marketing Junior',
                'subtitulo' => 'Ideal para pequeños emprendimientos',
                'precio' => 1000.00,
                'moneda' => 'BS',
                'periodo_facturacion' => 'mes',
                'orden' => 1,
                'activo' => true,
                'descripcion' => 'Incluye lo esencial para iniciar tu presencia digital.',
                'caracteristicas' => [
                    ['nombre' => 'Creación de línea gráfica', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'mensual', 'orden' => 1, 'es_destacado' => false],
                    ['nombre' => 'Fotografía de producto o servicio', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 2, 'es_destacado' => true],
                    ['nombre' => '12 diseños para posts (3 por semana)', 'descripcion' => null, 'cantidad' => 3, 'frecuencia' => 'semanal', 'orden' => 3, 'es_destacado' => true],
                    ['nombre' => 'Informe de rendimiento mensuales', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 4, 'es_destacado' => false],
                    ['nombre' => 'Community manager', 'descripcion' => 'accascasfds', 'cantidad' => 1, 'frecuencia' => null, 'orden' => 5, 'es_destacado' => false],
                    ['nombre' => 'Elaboración de plan de contenido', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 6, 'es_destacado' => false],
                ],
            ],
            [
                'nombre' => 'Marketing Pro',
                'subtitulo' => 'Para empresas en crecimiento',
                'precio' => 1500.00,
                'moneda' => 'BS',
                'periodo_facturacion' => 'mes',
                'orden' => 2,
                'activo' => true,
                'descripcion' => 'Campañas optimizadas con análisis de rendimiento y soporte dedicado.',
                'caracteristicas' => [
                    ['nombre' => '16 diseños para posts (4 posts por semana)', 'descripcion' => 'Reporte detallado del rendimiento mensual de las campañas.', 'cantidad' => 4, 'frecuencia' => 'semanal', 'orden' => 1, 'es_destacado' => true],
                    ['nombre' => 'Creación de línea gráfica', 'descripcion' => null, 'cantidad' => 2, 'frecuencia' => 'mensual', 'orden' => 2, 'es_destacado' => false],
                    ['nombre' => 'Video publicitario(Grabado y producido) 1 vez al mes', 'descripcion' => 'Campañas segmentadas en Facebook, Instagram y Google Ads.', 'cantidad' => 1, 'frecuencia' => 'mensual', 'orden' => 3, 'es_destacado' => false],
                    ['nombre' => 'Diseño gráfico profesional', 'descripcion' => 'Diseños personalizados para publicaciones y anuncios.', 'cantidad' => 1, 'frecuencia' => 'semanal', 'orden' => 4, 'es_destacado' => false],
                    ['nombre' => 'Un gif por semana', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'semanal', 'orden' => 5, 'es_destacado' => false],
                    ['nombre' => 'Catálogo digital en Whatsapp Business (1 vez al mes)', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'mensual', 'orden' => 6, 'es_destacado' => false],
                    ['nombre' => 'Informe de rendimiento mensuales', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'mensual', 'orden' => 7, 'es_destacado' => false],
                    ['nombre' => 'Community manager', 'descripcion' => 'accascasfds', 'cantidad' => 1, 'frecuencia' => null, 'orden' => 8, 'es_destacado' => false],
                    ['nombre' => 'Elaboración de plan de contenido', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 9, 'es_destacado' => false],
                ],
            ],
            [
                'nombre' => 'Marketing Super Pro',
                'subtitulo' => 'Estrategia completa de alto impacto',
                'precio' => 1900.00,
                'moneda' => 'BS',
                'periodo_facturacion' => 'mes',
                'orden' => 3,
                'activo' => true,
                'descripcion' => 'Incluye gestión avanzada, publicidad multicanal y reportes personalizados.',
                'caracteristicas' => [
                    ['nombre' => '18 diseños para posts (6 posts por semana)', 'descripcion' => null, 'cantidad' => 6, 'frecuencia' => 'semanal', 'orden' => 1, 'es_destacado' => true],
                    ['nombre' => 'Creación de línea gráfica', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 2, 'es_destacado' => false],
                    ['nombre' => '2 Videos publicitario (Grabado y producido) 1 vez al mes', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'mensual', 'orden' => 3, 'es_destacado' => false],
                    ['nombre' => 'Fotografía de producto o servicio', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'mensual', 'orden' => 4, 'es_destacado' => false],
                    ['nombre' => 'Tik tok', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => 'semanal', 'orden' => 5, 'es_destacado' => false],
                    ['nombre' => 'Dos Gif por semana', 'descripcion' => null, 'cantidad' => 2, 'frecuencia' => 'semanal', 'orden' => 6, 'es_destacado' => false],
                    ['nombre' => 'Catálogo digital en Whatsapp Business (1 vez al mes)', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 7, 'es_destacado' => false],
                    ['nombre' => 'Creación o actualización de página web', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 8, 'es_destacado' => false],
                    ['nombre' => 'Informe de rendimiento mensuales', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 9, 'es_destacado' => false],
                    ['nombre' => 'Community manager', 'descripcion' => 'accascasfds', 'cantidad' => 1, 'frecuencia' => null, 'orden' => 10, 'es_destacado' => false],
                    ['nombre' => 'Elaboración de plan de contenido', 'descripcion' => null, 'cantidad' => 1, 'frecuencia' => null, 'orden' => 11, 'es_destacado' => false],
                ],
            ],
        ];

        foreach ($planes as $data) {
            $caracteristicas = $data['caracteristicas'];
            unset($data['caracteristicas']);

            $plan = Plan::withTrashed()->firstOrNew(['nombre' => $data['nombre']]);
            $plan->fill($data);
            $plan->deleted_at = null;
            $plan->save();

            PlanCaracteristica::where('plan_id', $plan->id)->delete();

            foreach ($caracteristicas as $caracteristicaData) {
                $caracteristica = Caracteristica::withTrashed()->firstOrNew([
                    'nombre' => $caracteristicaData['nombre'],
                ]);

                $caracteristica->descripcion = $caracteristicaData['descripcion'];
                $caracteristica->deleted_at = null;
                $caracteristica->save();

                PlanCaracteristica::create([
                    'plan_id' => $plan->id,
                    'caracteristica_id' => $caracteristica->id,
                    'cantidad' => $caracteristicaData['cantidad'],
                    'frecuencia' => $caracteristicaData['frecuencia'],
                    'orden' => $caracteristicaData['orden'],
                    'es_destacado' => $caracteristicaData['es_destacado'],
                ]);
            }
        }
    }
}