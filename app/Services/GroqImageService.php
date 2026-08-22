<?php

namespace App\Services;

use App\Models\Tarea;
use App\Models\PlanMarketing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqImageService
{
    /**
     * Genera un texto publicitario (copy) basado en el Plan de Marketing y el contexto de la campaña.
     * Ya no analiza la imagen directamente debido a limitaciones del modelo.
     *
     * @param int $tareaId
     * @return string|null
     */
    public function generateCopyFromImage(int $tareaId): ?string
    {
        // 1. Obtener la tarea con el contexto de la empresa y campaña
        $tarea = Tarea::with([
            'campania.cliente.empresas'
        ])->findOrFail($tareaId);

        $empresa = $tarea->campania?->cliente?->empresas?->first();

        if (!$empresa) {
            Log::error("No se encontró una empresa asociada a la tarea ID: {$tareaId}");
            return 'Error: No se pudo encontrar el contexto de la empresa.';
        }

        // 2. Obtener el Plan de Marketing más reciente de la empresa
        $planMarketing = PlanMarketing::where('empresa_id', $empresa->id)
            ->latest()
            ->first();

        $contextoPlanMarketing = SocialContentPolicy::sanitize(
            $planMarketing ? $planMarketing->contenido : 'No hay un plan de marketing generado aun.'
        );
        $descripcionCampania = SocialContentPolicy::sanitize((string) $tarea->campania->descripcion);
        $reglasCanales = SocialContentPolicy::promptRules();

        // 3. Construir el contexto enriquecido
        $contextoAdicional = <<<EOT
        ---
        **DATOS DE LA EMPRESA:**
        Nombre: {$empresa->nombre_empresa}
        Tipo: {$empresa->tipo_empresa}
        
        **PLAN DE MARKETING ESTRATÉGICO (RESUMEN):**
        {$contextoPlanMarketing}
        
        **CAMPAÑA ACTUAL:** {$tarea->campania->nombre}
        **DESCRIPCIÓN DE LA CAMPAÑA:** {$descripcionCampania}
        **TAREA ESPECÍFICA:** {$tarea->nombre}
        ---
        EOT;

        // 4. Construir el prompt para Groq
        $prompt = <<<EOT
        Eres un experto copywriter de redes sociales y estratega de marca. Tu tarea es crear 3 opciones de texto publicitario (copy) altamente persuasivos.

        Debes basarte estrictamente en el **Plan de Marketing** de la empresa y los objetivos de la **Campaña Actual** que se te proporcionan a continuación.

        {$contextoAdicional}

        **INSTRUCCIONES:**
        {$reglasCanales}
        1. Comprende el tono de voz y los objetivos definidos en el Plan de Marketing.
        2. Alinea el mensaje con la descripción de la campaña y la tarea específica.
        3. Genera 3 opciones de copy que sean creativas, profesionales y orientadas a la conversión.
        4. **IMPORTANTE:** Incluye hashtags relevantes al final de cada opción (mínimo 3 por opción).
        5. Usa un lenguaje que conecte con el público objetivo de la empresa.

        **EJEMPLO DE FORMATO DE RESPUESTA:**
        1. [Texto del copy 1] ... #Hashtag1 #Hashtag2 #Hashtag3
        2. [Texto del copy 2] ... #Hashtag1 #Hashtag2 #Hashtag3
        3. [Texto del copy 3] ... #Hashtag1 #Hashtag2 #Hashtag3

        Responde ÚNICAMENTE con las 3 opciones de copy numeradas. No añadas introducciones ni explicaciones adicionales.
        EOT;

        // 5. Preparar la petición para Groq (Texto puro con el modelo 70b)
        $payload = [
            'model' => config('services.groq.model'), // llama-3.3-70b-versatile
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7, // Un poco más de creatividad para los copys
            'stream' => false,
        ];

        // 6. Hacer la llamada a la API de Groq
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.key'),
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false,
            ])
            ->timeout(60)
            ->post(config('services.groq.url'), $payload);

        // 7. Procesar la respuesta
        if ($response->successful()) {
            $data = $response->json();
            return SocialContentPolicy::sanitize(
                $data['choices'][0]['message']['content'] ?? 'No se pudo generar el copy.'
            );
        } else {
            Log::error('Error en la API de Groq para generación de copy: ' . $response->body());
            return 'Hubo un error al generar el copy basado en el Plan de Marketing.';
        }
    }
}
