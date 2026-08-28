<?php

namespace App\Services;

use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CampaignBriefPrefillService
{
    public function __construct(
        private readonly ExecutiveSummaryFormatter $formatter,
        private readonly CampaignAudienceService $audienceService,
    ) {}

    public function build(Suscripcion $suscripcion, PlanMarketing $plan): array
    {
        $suscripcion->loadMissing('empresa.respuestasCuestionario.pregunta');
        $empresa = $suscripcion->empresa;
        $answers = $empresa?->respuestasCuestionario ?? collect();
        $planSections = collect($this->formatter->sections($plan->contenido));

        $product = $this->answer($answers, ['producto o servicio quieres', 'que vendes'])
            ?: $empresa?->descripcion;
        $goals = $this->answerItems($answers, ['objetivo principal', 'objetivos']);
        $audienceAnswer = $this->answerItems($answers, ['cliente ideal', 'publico objetivo', 'audiencia']);
        $differentiators = $this->answerItems($answers, ['hace diferente', 'diferencia a tu empresa']);
        $tone = $this->answerItems($answers, ['comunique tu marca', 'tono de comunicacion']);
        $instructions = $this->answer($answers, ['antes de publicar', 'indicacion especial', 'restriccion']);

        $strategy = $this->sectionText($planSections, ['estrategia general de contenido', 'direccion estrategica']);
        $audiencePlan = $this->sectionText($planSections, ['publico objetivo', 'audiencia objetivo']);
        $planText = $this->plainText($plan->contenido);

        $goalText = $this->naturalList($goals);
        $audienceText = $audiencePlan ?: $this->naturalList($audienceAnswer);
        $differenceText = $this->naturalList($differentiators);
        $toneText = $this->naturalList($tone);

        $descriptionParts = [];
        if ($goalText !== '') {
            $descriptionParts[] = 'La campaña ejecutará el plan de marketing para '.$goalText.'.';
        }
        if ($strategy !== '') {
            $descriptionParts[] = $strategy;
        }
        if ($instructions !== '') {
            $descriptionParts[] = 'Consideración previa del cliente: '.$instructions.'.';
        }

        return [
            'nombre' => $this->limit(trim('Campaña '.($product ?: $empresa?->nombre_empresa).($goals !== [] ? ': '.$goals[0] : '')), 100),
            'descripcion' => $this->limit(implode(' ', $descriptionParts), 1800),
            'objetivo_general' => $goalText !== ''
                ? $this->limit('Ejecutar una campaña digital orientada a '.$goalText.', conforme al plan de marketing aprobado.', 2500)
                : '',
            'publico_objetivo' => $this->limit($audienceText, 1900),
            'publicos_objetivo' => $this->audienceService->normalize([], $audienceText),
            'mensaje_principal' => $this->message($empresa?->nombre_empresa, $product, $differenceText),
            'tono_comunicacion' => $this->limit($toneText, 120),
            'canales' => collect(['Facebook', 'Instagram', 'TikTok', 'WhatsApp'])
                ->filter(fn (string $channel) => str_contains(Str::lower($planText), Str::lower($channel)))
                ->values()->all(),
            'indicadores' => $this->indicators($planText.' '.$goalText),
        ];
    }

    private function answer(Collection $answers, array $needles): string
    {
        foreach ($answers as $answer) {
            $question = $this->normalize((string) $answer->pregunta?->pregunta);
            if (collect($needles)->contains(fn (string $needle) => str_contains($question, $this->normalize($needle)))) {
                return trim((string) $answer->respuesta);
            }
        }

        return '';
    }

    private function answerItems(Collection $answers, array $needles): array
    {
        $value = $this->answer($answers, $needles);

        return collect(preg_split('/\s*\|\s*/u', $value) ?: [])
            ->map(fn (string $item) => trim($item, " \t\n\r\0\x0B.,;"))
            ->filter()->values()->all();
    }

    private function sectionText(Collection $sections, array $needles): string
    {
        $section = $sections->first(function (array $section) use ($needles) {
            $title = $this->normalize((string) ($section['titulo'] ?? ''));

            return collect($needles)->contains(fn (string $needle) => str_contains($title, $this->normalize($needle)));
        });

        return $section ? $this->plainHtml((string) ($section['html'] ?? '')) : '';
    }

    private function message(?string $company, ?string $product, string $differences): string
    {
        if (! $product && ! $differences) {
            return '';
        }

        $subject = $product ?: 'sus productos y servicios';
        $message = 'Presentar '.$subject.' como una solución de '.($company ?: 'la empresa');
        if ($differences !== '') {
            $message .= ', destacando '.$differences;
        }

        return $this->limit($message.', e invitar a la audiencia a conocerla y contactar a la marca.', 1500);
    }

    private function indicators(string $source): array
    {
        $source = $this->normalize($source);
        $indicators = [];
        $rules = [
            'Alcance' => ['alcance', 'reconocimiento', 'visibilidad'],
            'Interacciones' => ['interaccion', 'engagement', 'comentarios'],
            'Mensajes y consultas' => ['mensaje', 'consulta', 'lead'],
            'Conversiones' => ['conversion', 'ventas', 'clientes'],
            'Cumplimiento de publicaciones' => ['publicacion', 'calendario', 'frecuencia'],
        ];

        foreach ($rules as $indicator => $needles) {
            if (collect($needles)->contains(fn (string $needle) => str_contains($source, $needle))) {
                $indicators[] = $indicator;
            }
        }

        return $indicators ?: ['Alcance', 'Interacciones', 'Cumplimiento de publicaciones'];
    }

    private function naturalList(array $items): string
    {
        $items = array_values(array_filter(array_map('trim', $items)));
        if (count($items) < 2) {
            return $items[0] ?? '';
        }

        $last = array_pop($items);

        return implode(', ', $items).' y '.$last;
    }

    private function plainHtml(string $html): string
    {
        $html = str_ireplace(['</p>', '</li>', '<br>', '<br/>', '<br />'], ['. ', '. ', ' ', ' ', ' '], $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/([.!?])\.\s+/u', '$1 ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    private function plainText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }

    private function limit(string $value, int $length): string
    {
        return Str::limit(trim($value), $length, '');
    }
}
