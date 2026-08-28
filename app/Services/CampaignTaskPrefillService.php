<?php

namespace App\Services;

use App\Models\PlanMarketing;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class CampaignTaskPrefillService
{
    public function build(PlanMarketing $plan, CarbonInterface|string $startsAt, CarbonInterface|string $endsAt): array
    {
        $calendar = $this->calendarSection($plan->contenido);
        if ($calendar === '') {
            return [];
        }

        $start = Carbon::parse($startsAt)->startOfDay();
        $end = Carbon::parse($endsAt)->startOfDay();
        $tableTasks = $this->tableTasks($calendar, $start, $end);
        if ($tableTasks !== []) {
            return array_slice($tableTasks, 0, 20);
        }

        $weeks = $this->weeks($calendar);
        $tasks = [];

        foreach (array_slice($weeks, 0, 5) as $index => $week) {
            $weekStart = $start->copy()->addWeeks($index);
            if ($weekStart->gt($end)) {
                break;
            }

            $weekEnd = $weekStart->copy()->addDays(6)->min($end);
            $content = $this->limit($this->plain($week['content']), 520);
            $label = $week['title'] ?: 'Semana '.($index + 1);

            $tasks[] = [
                'titulo' => $this->limit('Contenido - '.$label, 100),
                'descripcion' => $this->limit($content, 700),
                'entregable' => $this->limit('Contenido de '.$label.' aprobado y listo para publicar.', 1500),
                'fecha_inicio' => $weekStart->toDateString(),
                'fecha_limite' => $weekEnd->toDateString(),
                'prioridad' => 'media',
                'rol_sugerido' => 'Diseñador',
                'roles_sugeridos' => ['Diseñador', 'Community Manager'],
                'tipo_contenido' => 'post',
                'requiere_aprobacion' => true,
                'visible_cliente' => true,
            ];
        }

        return array_slice($tasks, 0, 10);
    }

    private function tableTasks(string $calendar, Carbon $start, Carbon $end): array
    {
        $tasks = [];

        foreach (explode("\n", $calendar) as $line) {
            if (! preg_match('/^\s*\|/', $line) || preg_match('/^\s*\|?[\s:|-]+\|?\s*$/u', $line)) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($line, " \t|")));
            if (count($cells) < 5) {
                continue;
            }

            $week = (int) preg_replace('/\D+/u', '', $cells[0]);
            if ($week < 1) {
                continue;
            }

            $theme = $this->plain($cells[1]);
            $objective = $this->plain($cells[2]);
            $cta = $this->plain($cells[3]);
            $pieces = preg_split('/\s*<br\s*\/?>(?:\s*)/iu', $cells[4]) ?: [];

            foreach ($pieces as $piece) {
                if (! preg_match('/^\s*\d+[.)]\s*\*\*(.+?)\*\*\s*[\x{2013}\x{2014}-]\s*(.+)$/us', trim($piece), $match)) {
                    continue;
                }

                $day = $this->plain($match[1]);
                $idea = $this->plain($match[2]);
                $type = $this->contentType($idea);
                $date = $start->copy()->addWeeks($week - 1)->addDays($this->dayOffset($day));
                if ($date->lt($start) || $date->gt($end)) {
                    continue;
                }

                $tasks[] = [
                    'titulo' => $this->limit(Str::ucfirst($type).' · Semana '.$week.' · '.$day, 100),
                    'descripcion' => $this->limit(
                        $this->sentence($idea).' '.$this->sentence('Tema: '.$theme).' '.$this->sentence('Objetivo: '.$objective).' '.$this->sentence('CTA: '.$cta),
                        700
                    ),
                    'entregable' => $this->limit(Str::ucfirst($type).' aprobado y listo para publicar.', 1500),
                    'fecha_inicio' => $date->toDateString(),
                    'fecha_limite' => $date->toDateString(),
                    'prioridad' => 'media',
                    'rol_sugerido' => 'Diseñador',
                    'roles_sugeridos' => ['Diseñador', 'Community Manager'],
                    'tipo_contenido' => $type,
                    'requiere_aprobacion' => true,
                    'visible_cliente' => true,
                ];
            }
        }

        return $tasks;
    }

    private function contentType(string $idea): string
    {
        $idea = $this->normalize($idea);

        return match (true) {
            str_contains($idea, 'carrusel'), str_contains($idea, 'carousel') => 'carrusel',
            str_contains($idea, 'reel'), str_contains($idea, 'video') => 'reel',
            str_contains($idea, 'historia'), str_contains($idea, 'story') => 'historia',
            str_contains($idea, 'guion'), str_contains($idea, 'script') => 'guion',
            default => 'post',
        };
    }

    private function dayOffset(string $day): int
    {
        return match (true) {
            str_contains($this->normalize($day), 'martes') => 1,
            str_contains($this->normalize($day), 'miercoles') => 2,
            str_contains($this->normalize($day), 'jueves') => 3,
            str_contains($this->normalize($day), 'viernes') => 4,
            str_contains($this->normalize($day), 'sabado') => 5,
            str_contains($this->normalize($day), 'domingo') => 6,
            default => 0,
        };
    }

    private function calendarSection(string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        preg_match_all('/^##(?!#)\s*(.+?)\s*$\n(.*?)(?=^##(?!#)\s|\z)/msu', $content, $sections, PREG_SET_ORDER);

        foreach ($sections as $section) {
            $title = $this->normalize(preg_replace('/^[\s*_`#]*(?:\d+[\s.)\-:]*)?/u', '', $section[1]) ?? '');
            if (str_contains($title, 'calendario operativo') || str_contains($title, 'cronograma operativo')) {
                return trim($section[2]);
            }
        }

        return '';
    }

    private function weeks(string $calendar): array
    {
        preg_match_all('/^###\s*(.+?)\s*$\n(.*?)(?=^###\s|\z)/msu', $calendar, $matches, PREG_SET_ORDER);
        $weeks = collect($matches)->map(fn (array $match) => [
            'title' => trim(preg_replace('/^[\s*_`#]*(?:\d+[\s.)\-:]*)?/u', '', $match[1]) ?? ''),
            'content' => trim($match[2]),
        ])->filter(fn (array $week) => $week['content'] !== '')->values()->all();

        return $weeks ?: [['title' => 'Calendario mensual', 'content' => $calendar]];
    }

    private function plain(string $content): string
    {
        $content = preg_replace('/^\s*\|?[\s:|-]+\|?\s*$/mu', '', $content) ?? $content;
        $content = preg_replace('/^\s*(?:[-*+] |\d+[.)]\s*)/mu', '', $content) ?? $content;
        $content = preg_replace('/[*_`#|]+/u', ' ', $content) ?? $content;
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

        if ($text !== '' && ! preg_match('/[.!?][”"]?$/u', $text)) {
            $lastStop = max(
                mb_strrpos($text, '.') ?: 0,
                mb_strrpos($text, '!') ?: 0,
                mb_strrpos($text, '?') ?: 0,
            );
            if ($lastStop >= (int) (mb_strlen($text) * .65)) {
                $text = mb_substr($text, 0, $lastStop + 1);
            }
        }

        return $text;
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii(trim($value)));
    }

    private function limit(string $value, int $length): string
    {
        return Str::limit(trim($value), $length, '');
    }

    private function sentence(string $value): string
    {
        return rtrim(trim($value), '.').'.';
    }
}
