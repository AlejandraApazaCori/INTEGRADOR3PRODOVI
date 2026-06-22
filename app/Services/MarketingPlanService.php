<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketingPlanService
{
    /**
     * Genera un plan de marketing personalizado basado en el resumen ejecutivo y las caracteristicas del plan.
     *
     * @param string $nombreEmpresa
     * @param string $resumenEjecutivo
     * @param array $caracteristicasPlan
     * @param array $planContexto
     * @return string|null
     */
    public function generateMarketingPlan(
        string $nombreEmpresa,
        string $resumenEjecutivo,
        array $caracteristicasPlan,
        array $planContexto = []
    ): ?string {
        // 1. Normalizar el resumen ejecutivo para usarlo como fuente principal de contexto.
        $resumenEjecutivoLimpio = trim($resumenEjecutivo);
        if ($resumenEjecutivoLimpio === '') {
            $resumenEjecutivoLimpio = 'No se proporciono un resumen ejecutivo utilizable.';
        }

        // 2. Normalizar el contexto del plan contratado.
        $nombrePlan = trim((string) ($planContexto['nombre'] ?? ''));
        $descripcionPlan = trim((string) ($planContexto['descripcion'] ?? ''));
        $periodoFacturacion = trim((string) ($planContexto['periodo_facturacion'] ?? ''));
        $precioPlan = $planContexto['precio'] ?? null;
        $monedaPlan = trim((string) ($planContexto['moneda'] ?? ''));

        $lineasPlan = [];
        if ($nombrePlan !== '') {
            $lineasPlan[] = '- Nombre del plan: ' . $nombrePlan;
        }
        if ($descripcionPlan !== '') {
            $lineasPlan[] = '- Descripcion comercial del plan: ' . $descripcionPlan;
        }
        if ($periodoFacturacion !== '') {
            $lineasPlan[] = '- Periodo de facturacion: ' . $periodoFacturacion;
        }
        if ($precioPlan !== null && $precioPlan !== '') {
            $lineasPlan[] = '- Precio registrado del plan: ' . $precioPlan . ($monedaPlan !== '' ? ' ' . $monedaPlan : '');
        }

        $contextoGeneralPlan = !empty($lineasPlan)
            ? implode("\n", $lineasPlan)
            : '- No se recibio contexto general del plan.';

        // 3. Normalizar y construir el contexto detallado de los recursos contratados.
        $lineasContexto = [];
        $caracteristicasNormalizadas = [];

        foreach ($caracteristicasPlan as $caracteristica) {
            $nombre = trim((string) ($caracteristica['nombre'] ?? ''));

            if ($nombre === '') {
                continue;
            }

            $cantidad = $caracteristica['cantidad'] ?? null;
            $cantidad = is_numeric($cantidad) ? (int) $cantidad : null;

            $unidad = trim((string) ($caracteristica['unidad'] ?? ''));
            $frecuencia = trim((string) ($caracteristica['frecuencia'] ?? ''));
            $descripcion = trim((string) ($caracteristica['descripcion'] ?? ''));
            $orden = $caracteristica['orden'] ?? null;
            $esDestacado = (bool) ($caracteristica['es_destacado'] ?? false);

            $partes = ["recurso: {$nombre}"];

            if ($cantidad !== null && $cantidad > 0) {
                $textoCantidad = "cantidad contratada: {$cantidad}";
                if ($unidad !== '') {
                    $textoCantidad .= " {$unidad}";
                }
                $partes[] = $textoCantidad;
            } else {
                $partes[] = 'cantidad contratada: no especificada';
            }

            $partes[] = $frecuencia !== ''
                ? "frecuencia: {$frecuencia}"
                : 'frecuencia: no especificada';

            if ($descripcion !== '') {
                $partes[] = "detalle funcional: {$descripcion}";
            }

            if ($orden !== null && $orden !== '') {
                $partes[] = 'orden interno: ' . $orden;
            }

            if ($esDestacado) {
                $partes[] = 'prioridad comercial: destacado';
            }

            $lineasContexto[] = '- ' . implode(' | ', $partes);

            $caracteristicasNormalizadas[] = [
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'unidad' => $unidad !== '' ? $unidad : null,
                'frecuencia' => $frecuencia !== '' ? $frecuencia : null,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'orden' => $orden,
                'es_destacado' => $esDestacado,
            ];
        }

        $contextoPlan = !empty($lineasContexto)
            ? implode("\n", $lineasContexto)
            : '- No se recibieron recursos contratados con detalle suficiente.';

        $recursosJson = json_encode(
            $caracteristicasNormalizadas,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        // 4. Construir el prompt para generar el plan de marketing.
        $prompt = <<<EOT
Actua como estratega senior de marketing digital, contenido y operaciones comerciales. Debes crear un plan de marketing estrategico-operativo para "{$nombreEmpresa}" que aproveche al maximo dos fuentes de verdad: el brief estrategico ejecutivo y el plan contratado por el cliente.

OBJETIVO DEL DOCUMENTO:
- Convertir el brief ejecutivo en un plan accionable.
- Aprovechar cada detalle confirmado del resumen ejecutivo.
- Aprovechar cada detalle confirmado del plan contratado.
- No inventar recursos, cifras ni acciones fuera del alcance contratado.

FORMATO DE SALIDA:
- Responde en espanol.
- Usa Markdown.
- Tono profesional, claro, accionable y especifico.
- Maximo 1,800 palabras.

FUENTE 1: BRIEF ESTRATEGICO EJECUTIVO
---
{$resumenEjecutivoLimpio}
---

FUENTE 2: CONTEXTO GENERAL DEL PLAN CONTRATADO
---
{$contextoGeneralPlan}
---

FUENTE 3: RECURSOS CONTRATADOS - RESUMEN LEGIBLE
---
{$contextoPlan}
---

FUENTE 4: RECURSOS CONTRATADOS - DATOS ESTRUCTURADOS
```json
{$recursosJson}
```

PRIORIDADES DE INTERPRETACION:
1. El brief ejecutivo define negocio, publico, problemas, objetivos, recursos y contexto general.
2. El plan contratado define el limite operativo real del plan de marketing.
3. Si el brief sugiere oportunidades que el plan no cubre, no las conviertas en acciones ejecutables fuera del alcance contratado.
4. Si el plan contratado incluye un recurso, debes reflejarlo de forma concreta en el plan final.
5. Si falta informacion importante en el brief o en el plan, declaralo claramente como supuesto minimo.

REGLAS ESTRICTAS:
1. Usa unicamente los recursos presentes en el plan contratado.
2. Respeta exactamente cantidades, frecuencias y alcances cuando esten disponibles.
3. Si el plan indica recursos semanales, distribuyelos exactamente por cada una de las 4 semanas del mes.
4. Si el plan indica recursos mensuales, distribuyelos de forma logica y explicita dentro del mes.
5. No agregues servicios, canales, formatos, piezas, campañas o acciones que no esten incluidos en los recursos listados.
6. No propongas publicidad pagada si el plan no la incluye explicitamente.
7. No inventes presupuestos, porcentajes, ROI, ingresos, CAC, ROAS ni proyecciones financieras.
8. No inventes competidores, datos externos de mercado ni resultados esperados.
9. Si no hay datos historicos suficientes, formula objetivos medibles sin inventar linea base numerica.
10. Si una caracteristica no tiene cantidad o frecuencia clara, declarala en "Supuestos utilizados" y usa una interpretacion prudente minima.
11. Debes aprovechar el contenido del brief ejecutivo de forma visible: propuesta de valor, publico, problemas, objetivos, diferenciadores, canales actuales, recursos y restricciones deben reflejarse en la estrategia.
12. Evita generalidades. Cada seccion debe conectar el brief con los recursos reales contratados.

ENTREGABLE OBLIGATORIO:

## 1 Diagnostico estrategico
- Resume la situacion del negocio usando el brief ejecutivo.
- Incluye propuesta de valor, oportunidad principal, reto principal y direccion estrategica general.
- Diferencia claramente entre dato proporcionado y supuesto si aplica.

## 2 Supuestos utilizados
- Enumera unicamente los supuestos necesarios por falta de informacion.
- Indica que parte viene del brief y que parte se esta suponiendo.
- Si no hay datos financieros o competitivos suficientes, dilo explicitamente.

## 3 Publico objetivo
- Define segmentos o buyer personas realistas a partir del brief ejecutivo.
- Para cada segmento indica necesidad principal, motivacion, objecion probable y contenido/canal mas util.
- Usa solo lo que se puede sostener con el brief.

## 4 Objetivos SMART
- Define entre 3 y 5 objetivos SMART a 3 meses.
- Deben alinearse estrictamente con los recursos contratados y con los objetivos declarados por el cliente en el brief.
- No inventes cifras historicas ni metas numericas arbitrarias.

## 5 Estrategia general de contenido
- Explica la logica editorial general.
- Define pilares de contenido conectados con problemas, oportunidades, propuesta de valor y publico objetivo del brief.
- Indica formatos y funcion de cada formato segun los recursos incluidos en el plan.
- No menciones formatos que no existan en el plan.

## 6 Embudo de marketing
- Divide en reconocimiento, consideracion y conversion.
- Para cada etapa indica objetivo, tipo de mensaje, formatos permitidos por el plan y CTA recomendado.
- Si existe catalogo de WhatsApp, fotografia, videos, GIFs, TikTok u otros recursos, explica su papel exacto en el embudo.

## 7 Calendario operativo mensual
Esta debe ser la seccion mas detallada.

Organiza obligatoriamente por:
- Semana 1
- Semana 2
- Semana 3
- Semana 4

En cada semana incluye, segun el plan:
- tema central;
- objetivo de la semana;
- CTA principal;
- piezas exactas a ejecutar segun cantidades y frecuencias contratadas;
- idea concreta de cada pieza;
- relacion de cada pieza con el publico y objetivo del brief.

Reglas operativas del calendario:
- Si el plan contratado indica 6 posts por semana, debes generar exactamente 6 ideas de posts por cada semana.
- Si indica 2 GIFs por semana, debes generar exactamente 2 ideas por semana.
- Si incluye videos mensuales, define tema, objetivo, guion breve y CTA para cada video.
- Si incluye TikTok semanal, define ideas concretas por semana y respeta exactamente la cantidad.
- Si incluye fotografia mensual, indica que fotos tomar y en que piezas se reutilizaran.
- Si incluye catalogo de WhatsApp, indica que actualizar, que productos o servicios priorizar y como usarlo para conversion.
- No uses rangos vagos cuando exista cantidad exacta.

## 8 Uso exacto de recursos contratados
- Haz una lista recurso por recurso.
- Para cada recurso contratado indica:
  - cantidad y frecuencia contratada si existe,
  - accion concreta a ejecutar,
  - objetivo dentro del plan,
  - entregable esperado,
  - relacion con el brief ejecutivo.
- Si un recurso no tiene cantidad o frecuencia, aclara el supuesto usado.

## 9 KPIs y medicion
- Define entre 5 y 8 KPIs realistas.
- Deben corresponder a los recursos realmente contratados y a los objetivos del brief.
- Indica como medir cada KPI.
- Si no hay datos financieros, usa indicadores no financieros como interaccion, clics a WhatsApp, consultas, leads, alcance, guardados, reproducciones, conversiones a contacto o avance del catalogo.

## 10 Recomendaciones finales
- Cierra con recomendaciones accionables y priorizadas.
- No agregues nuevas tacticas fuera del plan contratado.
- Las recomendaciones deben mejorar la ejecucion de lo ya contratado, no ampliar el alcance.

CRITERIO DE CALIDAD:
- El resultado debe sentirse como un plan operativo listo para ejecutar.
- Debe ser especifico y no generico.
- Debe reflejar cada detalle util del brief ejecutivo.
- Debe reflejar cada recurso contratado de forma concreta.
- Si falta informacion, declara el supuesto y minimiza la invencion.
EOT;

        // 5. Preparar y hacer la llamada a la API de Groq
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.key'),
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false,
            ])
            ->timeout(180)
            ->post(config('services.groq.url'), [
                'model' => config('services.groq.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2,
                'stream' => false,
            ]);

        // 6. Procesar la respuesta
        if ($response->successful()) {
            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? 'No se pudo generar el plan de marketing.';
        }

        Log::error('Error en la API de Groq al generar plan de marketing: ' . $response->body());

        return 'Hubo un error e intentalo nuevamente.';
    }
}