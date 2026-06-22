<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    /**
     * Genera un brief estrategico ejecutivo basado en las respuestas de un cuestionario.
     *
     * @param string $nombreEmpresa
     * @param array $respuestas Formato: [['pregunta' => '...', 'respuesta' => '...'], ...]
     * @return string|null
     */
    public function generateSummary(string $nombreEmpresa, array $respuestas): ?string
    {
        // 1. Construir el contexto para la IA con datos limpios y solo respuestas utiles.
        $bloquesContexto = [];

        foreach ($respuestas as $item) {
            $pregunta = trim((string) ($item['pregunta'] ?? ''));
            $respuesta = trim((string) ($item['respuesta'] ?? ''));

            if ($pregunta === '' && $respuesta === '') {
                continue;
            }

            if ($pregunta === '') {
                $pregunta = 'Pregunta no especificada';
            }

            if ($respuesta === '') {
                continue;
            }

            $bloquesContexto[] = "Pregunta: {$pregunta}\nRespuesta: {$respuesta}";
        }

        $contextoCuestionario = implode("\n\n", $bloquesContexto);

        if ($contextoCuestionario === '') {
            $contextoCuestionario = 'No se recibieron respuestas validas del cuestionario.';
        }

        // 2. Construir el prompt preciso para generar un brief ejecutivo.
        $prompt = <<<EOT
Actua como un consultor senior de marketing estrategico y analisis comercial. Tu tarea es redactar un "Brief estrategico ejecutivo" para la empresa "{$nombreEmpresa}" a partir de un cuestionario tipo brief.

OBJETIVO DEL DOCUMENTO:
- Este documento NO es un plan de marketing completo.
- Este documento debe servir como base para un generador posterior de plan de marketing.
- Debe resumir con claridad la situacion del negocio, sus recursos, su publico, sus objetivos y los vacios de informacion.
- Debe ser fiel al cuestionario y no inventar datos.

REGLA PRINCIPAL:
- Basate estrictamente en la informacion proporcionada en el cuestionario.
- Si un dato no fue proporcionado, no lo inventes.
- Si necesitas inferir algo menor para mantener coherencia, debes marcarlo claramente como "Supuesto".

FORMATO DE SALIDA:
- Responde en espanol.
- Usa Markdown.
- Manten un tono profesional, claro, sobrio y ejecutivo.
- Maximo 1,200 palabras.

ESTRUCTURA OBLIGATORIA:

## 1 Resumen general de la empresa
Describe brevemente que hace la empresa, a quien atiende y cual es su proposito principal.

## 2 Perfil del negocio
Incluye historia breve, ubicacion si fue mencionada, etapa actual del negocio, equipo si fue mencionado y situacion general.

## 3 Productos y servicios
Enumera los productos o servicios ofrecidos y senala cuales parecen ser los mas importantes segun el cuestionario.

## 4 Publico objetivo
Describe el cliente ideal usando solo los datos proporcionados: edad, tipo de persona, necesidades, motivaciones y dudas frecuentes.

## 5 Propuesta de valor y diferenciadores
Explica por que los clientes eligen la empresa y que la diferencia frente a la competencia.

## 6 Competencia y contexto de mercado
Resume unicamente lo que el cliente indico sobre sus competidores, mercado o contexto. No inventes nombres de competidores ni datos externos.

## 7 Marketing y ventas actuales
Describe los canales actuales, contenidos que publican, que les funciona mejor, como llegan los clientes y si hacen publicidad pagada.

## 8 Problemas y oportunidades detectadas
Enumera los principales problemas actuales y oportunidades de mejora detectadas en el brief.

## 9 Objetivos declarados por el cliente
Resume las metas de 6 a 12 meses usando las palabras del cliente cuando sea posible. No conviertas estos objetivos en metricas numericas si el cliente no dio cifras.

## 10 Recursos disponibles
Resume presupuesto, equipo actual y recursos mencionados. No hagas distribucion porcentual del presupuesto.

## 11 Informacion faltante o por validar
Lista la informacion que hace falta o que conviene validar antes de crear un plan de marketing mas preciso.
Incluye solo vacios realmente relevantes detectados a partir del cuestionario.
Si corresponde, puedes mencionar ejemplos como:
- ubicacion exacta o zona de atencion;
- ticket promedio real;
- capacidad maxima de atencion;
- calendario de inscripciones;
- testimonios disponibles;
- presupuesto mensual exacto;
- restricciones legales o de privacidad.

## 12 Conclusion ejecutiva
Cierra con un resumen breve de la situacion actual y de la direccion estrategica general recomendada, sin entrar todavia en calendario, tacticas detalladas ni plan de accion completo.

INSTRUCCIONES ESTRICTAS:
- No inventes cifras.
- No inventes porcentajes.
- No inventes presupuesto ni distribuciones presupuestarias.
- No inventes ROI.
- No inventes competidores especificos.
- No inventes resultados esperados.
- No inventes datos de mercado externos.
- No propongas un calendario de contenido.
- No generes un plan de marketing completo.
- No desarrolles tacticas demasiado detalladas.
- No recomiendes acciones que dependan de recursos no confirmados.
- Diferencia claramente entre "Dato proporcionado" y "Supuesto" cuando aplique.
- Si una respuesta del cuestionario esta vacia o no aporta informacion, ignorala o reflejala en "Informacion faltante o por validar".
- Si el cuestionario no contiene datos suficientes para una seccion, indicalo de forma profesional.
- El contenido debe ser util como insumo para un servicio posterior que generara el plan de marketing.

DATOS DEL CUESTIONARIO:
{$contextoCuestionario}
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
                'temperature' => 0.2,
                'stream' => false,
            ]);

        // 4. Procesar la respuesta
        if ($response->successful()) {
            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? 'No se pudo generar el resumen.';
        } else {
            Log::error('Error en la API de Groq: ' . $response->body());

            return 'Hubo un error al generar el resumen.';
        }
    }
}
