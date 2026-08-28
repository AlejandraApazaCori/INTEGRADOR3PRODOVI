<?php

namespace App\Services;

use Illuminate\Support\Str;

class CampaignAudienceService
{
    public function normalize(mixed $segments, ?string $fallback = null): array
    {
        $normalized = collect(is_array($segments) ? $segments : [])
            ->filter(fn ($segment) => is_array($segment))
            ->map(fn (array $segment) => [
                'tipo_edades' => Str::limit(trim((string) ($segment['tipo_edades'] ?? $segment['tipo'] ?? '')), 120, ''),
                'descripcion' => Str::limit($this->clean((string) ($segment['descripcion'] ?? '')), 600, ''),
            ])
            ->filter(fn (array $segment) => $segment['tipo_edades'] !== '' || $segment['descripcion'] !== '')
            ->take(10)
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : $this->parse((string) $fallback);
    }

    public function serialize(array $segments): string
    {
        return collect($this->normalize($segments))
            ->map(fn (array $segment) => trim($segment['tipo_edades'].($segment['descripcion'] !== '' ? ': '.$segment['descripcion'] : '')))
            ->filter()
            ->implode("\n");
    }

    public function parse(string $audience): array
    {
        $audience = $this->clean(preg_replace(
            '/\bSegmento\s+Necesidad principal\s+Motivaci[oó]n\s+Objeci[oó]n probable\s+Contenido\s*\/\s*Canal recomendado\b/ui',
            '',
            $audience
        ) ?? $audience);

        if ($audience === '') {
            return [];
        }

        $typographicAgeRange = '\(\s*\d{1,2}\s*[\-\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}]\s*\d{1,2}\s*(?:a|años?)?\s*\)';
        preg_match_all(
            '/(?<title>[^.!?]{2,120}?'.$typographicAgeRange.')\s*:?\s*(?<description>.*?)(?=\s+[^.!?]{2,120}?'.$typographicAgeRange.'|$)/ui',
            $audience,
            $typographicMatches,
            PREG_SET_ORDER
        );

        if ($typographicMatches !== []) {
            return collect($typographicMatches)->map(fn (array $match) => [
                'tipo_edades' => Str::limit(trim($match['title'], " \t\n\r\0\x0B–—-"), 120, ''),
                'descripcion' => Str::limit($this->clean($match['description']), 600, ''),
            ])->filter(fn (array $segment) => $segment['tipo_edades'] !== '')
                ->take(10)->values()->all();
        }

        $ageRange = '\(\s*\d{1,2}\s*[\-–‑]\s*\d{1,2}(?:\s*a)?\s*\)';
        preg_match_all(
            '/(?<title>[^.!?]{2,100}?'.$ageRange.')\s*(?<description>.*?)(?=\s+[^.!?]{2,100}?'.$ageRange.'|$)/u',
            $audience,
            $matches,
            PREG_SET_ORDER
        );

        if ($matches !== []) {
            return collect($matches)->map(fn (array $match) => [
                'tipo_edades' => Str::limit(trim($match['title'], " \t\n\r\0\x0B–—-"), 120, ''),
                'descripcion' => Str::limit($this->clean($match['description']), 600, ''),
            ])->filter(fn (array $segment) => $segment['tipo_edades'] !== '')
                ->take(10)->values()->all();
        }

        $lines = preg_split('/\s*[\r\n|]+\s*/u', $audience) ?: [];
        $segments = collect($lines)->map(function (string $line) {
            [$title, $description] = array_pad(preg_split('/\s*:\s*/u', $line, 2) ?: [], 2, '');

            return [
                'tipo_edades' => Str::limit(trim($title), 120, ''),
                'descripcion' => Str::limit($this->clean($description), 600, ''),
            ];
        })->filter(fn (array $segment) => $segment['tipo_edades'] !== '')->take(10)->values()->all();

        return $segments !== [] ? $segments : [[
            'tipo_edades' => 'Público principal',
            'descripcion' => Str::limit($audience, 600, ''),
        ]];
    }

    private function clean(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value, " \t\n\r\0\x0B–—-");
    }
}
