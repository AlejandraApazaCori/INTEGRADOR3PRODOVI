<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketingPlanService
{
    /**
     * Genera un plan de marketing personalizado basado en el resumen ejecutivo y las características del plan.
     *
     * @param string $nombreEmpresa
     * @param string $resumenEjecutivo
     * @param array $caracteristicasPlan Formato: ['nombre' => 'Gestión de redes sociales', 'frecuencia' => 'semanal', ...]
     * @return string|null
     */
    public function generateMarketingPlan(string $nombreEmpresa, string $resumenEjecutivo, array $caracteristicasPlan): ?string
    {
        // 1. Construir el contexto de las características del plan
        $contextoPlan = "";
        foreach ($caracteristicasPlan as $caracteristica) {
            $contextoPlan .= "- {$caracteristica['nombre']}: {$caracteristica['frecuencia']}\n";
        }

        // 2. Construir el prompt para generar el plan de marketing
        $prompt = <<<EOT
        Actúa como un experto estratega de marketing digital. Tu tarea es crear un plan de marketing detallado, profesional y altamente personalizado para una empresa llamada "{$nombreEmpresa}".

        Este plan debe estar específicamente diseñado para ejecutarse con los siguientes recursos y servicios, que están incluidos en la suscripción del cliente:
        ---
        RECURSOS DISPONIBLES (FRECUENCIA):
        {$contextoPlan}
        ---

        El plan debe seguir la siguiente estructura y formato exacto. Utiliza los encabezados (##) como se indica a continuación. La respuesta final debe estar en español.

        ## 1 Análisis de la situación actual
        Basado en el resumen ejecutivo proporcionado, realiza un análisis DAFO (Debilidades, Amenazas, Fortalezas, Oportunidades) conciso. Identifica claramente la propuesta única de venta (USP) de la empresa.

        ## 2 Análisis de la competencia
        Describe quiénes son los competidores más probables y cuál podría ser la posición de {$nombreEmpresa} en el mercado en comparación con ellos.

        ## 3 Objetivos SMART
        Define 3-5 objetivos de marketing SMART (Específicos, Medibles, Alcanzables, Relevantes, con Plazo) que la empresa debería perseguir en los próximos 3-6 meses, alineados con los recursos disponibles.

        ## 4 Plan de actuación: Estrategias de marketing
        Este es el apartado más importante. Detalla las estrategias y tácticas a seguir, distribuidas según las "4 P del marketing" y adaptadas estrictamente a los recursos disponibles mencionados anteriormente.

        ### Estrategia de producto
        (Un párrafo corto)
        Recomendaciones sobre el producto o servicio basadas en el análisis.

        ### Estrategia de precio
        (Un párrafo corto)
        Sugerencias de precios, descuentos o promociones.

        ### Estrategia de ventas y distribución
        (Un párrafo corto)
        Cómo se venderá y distribuirá el producto/servicio.

        ### Estrategia de promoción y comunicación
        (Este es el apartado clave)
        Detalla un plan de acción mensual o trimestral, especificando CÓMO se usarán los recursos disponibles. Por ejemplo:
        - **Gestión de redes social (semanal):** "Publicar 3 veces por semana en Instagram y Facebook, enfocándose en [tema X]. Crear historias interactivas los martes y jueves."
        - **Análisis de métricas (mensual):** "El primer día de cada mes, revisar las métricas de engagement en redes y el tráfico web para ajustar la estrategia."
        - **Publicidad pagada (mensual):** "Destinar [porcentaje del presupuesto si aplica, o 'el presupuesto asignado'] a campañas en Facebook Ads dirigidas a [público objetivo] para promocionar [producto/servicio]."
        - **Diseño gráfico profesional (mensual):** "Crear 4 diseños gráficos profesionales al mes para las campañas principales, incluyendo [tipo de contenido]."

        ## 5# Fidelización
        Propón 2-3 estrategias de fidelización de clientes que la empresa puede implementar con sus recursos actuales.

        ## 6# KPIs y Medición de Resultados
        Define 4-6 KPIs (Indicadores Clave de Rendimiento) específicos para medir el éxito de este plan. Incluye cómo se medirán (ej. "a través de Google Analytics", "con las herramientas nativas de Instagram", etc.). Define también un ROI (Retorno de la Inversión) mínimo esperado.

        ## 7# Conclusiones
        (Dos o tres frases)
        Un resumen final de los pasos a seguir y el impacto esperado en el negocio.

        ---
        DATOS BASE DE LA EMPRESA (RESUMEN EJECUTIVO):
        {$resumenEjecutivo}
        ---

        INSTRUCCIONES ADICIONALES:
        - El plan debe ser 100% coherente con los recursos disponibles. No propongas acciones de publicidad pagada si el plan no la incluye, por ejemplo.
        - El tono debe ser profesional, estratégico y enfocado en la acción.
        - El plan completo no debe superar las 1,500 palabras.
        EOT;

        // 3. Preparar y hacer la llamada a la API de Groq
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
                'temperature' => 0.3,
                'stream' => false,
            ]);

        // 4. Procesar la respuesta
        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? 'No se pudo generar el plan de marketing.';
        } else {
            Log::error('Error en la API de Groq al generar plan de marketing: ' . $response->body());
            return 'Hubo un error e inténtalo nuevamente.';
        }
    }
}