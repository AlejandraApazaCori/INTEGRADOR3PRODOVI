<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    /**
     * Genera un resumen ejecutivo basado en las respuestas de un cuestionario.
     *
     * @param string $nombreEmpresa
     * @param array $respuestas Formato: [['pregunta' => '...', 'respuesta' => '...'], ...]
     * @return string|null
     */
    public function generateSummary(string $nombreEmpresa, array $respuestas): ?string
    {
        // 1. Construir el contexto para la IA
        $contextoCuestionario = "";
        foreach ($respuestas as $item) {
            $contextoCuestionario .= "Pregunta: {$item['pregunta']}\n";
            $contextoCuestionario .= "Respuesta: {$item['respuesta']}\n\n";
        }

        // 2. Construir el prompt (la instrucción detallada para el modelo)
        $prompt = <<<EOT
        Actúa como un experto en marketing digital y estrategia de negocios. Tu tarea es crear un resumen ejecutivo detallado, profesional y persuasivo para una empresa llamada "{$nombreEmpresa}".

        El resumen debe seguir la siguiente estructura y formato exacto. Utiliza los encabezados (##) como se indica a continuación. La respuesta final debe estar en español.

        ## Introducción
        (De dos a tres frases)
        Descripción general del proyecto, propósito del plan de marketing y beneficios clave para el cliente.

        ## Perfil de la empresa
        (Un párrafo corto)
        Historia de la empresa, estructura, base de clientes, cifras de ventas (si están disponibles), miembros del equipo y sus roles, y ubicación.

        ## Oportunidad de mercado
        (Un párrafo corto)
        Descripción general de la industria, tendencias del mercado, panorama competitivo, factores del mercado e innovaciones relevantes.

        ## Productos y servicios
        (Un párrafo corto)
        Descripción del producto o servicio principal, características y beneficios clave, y propuesta de venta única (USP).

        ## Objetivos y estrategia
        (Un párrafo)
        Demografía del público objetivo, estrategia promocional, prioridades de marketing, cronogramas y métodos de distribución propuestos.

        ## Presupuesto y KPI
        (De tres a cinco puntos en formato de lista)
        Proyecciones financieras (si es posible estimarlas), desglose presupuestario por actividad de marketing y métricas clave de éxito (KPIs) para medir el rendimiento.

        ## Conclusión
        (De dos a tres frases)
        Resumen de los objetivos principales, estrategias de implementación y un llamado a la acción (CTA) para revisar el plan de marketing completo.

        ---
        DATOS DE LA EMPRESA PARA BASAR EL RESUMEN:
        {$contextoCuestionario}
        ---

        INSTRUCCIONES ADICIONALES:
        - Genera el contenido para cada sección basándote estrictamente en la información proporcionada en los "DATOS DE LA EMPRESA".
        - Si falta información crucial para alguna sección (especialmente para "Presupuesto y KPI"), indícalo de manera profesional y sugiere cómo se podría obtener o estimar.
        - El tono debe ser profesional, optimista y enfocado en soluciones.
        - El resumen completo no debe superar las 1,000 palabras.
        EOT;

        // 3. Preparar y hacer la llamada a la API de Groq
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.key'),
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false,
            ])
            ->timeout(120)
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
            // Groq devuelve la respuesta en choices[0][message][content]
            return $data['choices'][0]['message']['content'] ?? 'No se pudo generar el resumen.';
        } else {
            Log::error('Error en la API de Groq: ' . $response->body());
            return 'Hubo un error al generar el resumen.';
        }
    }
}
