<?php

namespace App\Services;

use Illuminate\Support\Str;

class SocialContentPolicy
{
    public static function promptRules(): string
    {
        return <<<'RULES'
CANALES OBLIGATORIOS PARA TODO CONTENIDO GENERADO:
- Trabaja exclusivamente con Facebook, Instagram y TikTok.
- No menciones ni recomiendes WhatsApp, correo electronico, email, e-mail o Gmail.
- Todas las publicaciones, llamados a la accion, objetivos, tareas y metricas deben corresponder a Facebook, Instagram o TikTok.
- No uses tablas. Presenta la informacion como texto breve o listas simples.
RULES;
    }

    public static function containsExcludedChannel(string $content): bool
    {
        $normalized = Str::lower(Str::ascii($content));

        return preg_match('/whats\s*app|gmail|e-?mail|correo(?:s)?(?:\s+electronico(?:s)?)?/', $normalized) === 1;
    }

    public static function sanitize(string $content): string
    {
        $content = preg_replace(
            '/\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b/ui',
            '',
            $content
        );
        $content = preg_replace(
            '/\b(?:cat[aá]logo\s+de\s+Whats\s*App|clics?\s+a\s+Whats\s*App)\b/ui',
            'contenido e interacciones en Facebook, Instagram y TikTok',
            (string) $content
        );
        $content = preg_replace(
            '/\b(?:Whats\s*App|Gmail|e-?mail|correos?\s+electr[oó]nicos?|correos?)\b/ui',
            'Facebook, Instagram y TikTok',
            (string) $content
        );
        $content = preg_replace(
            '/(Facebook, Instagram y TikTok)(?:\s*(?:,|\/|y)\s*Facebook, Instagram y TikTok)+/ui',
            '$1',
            (string) $content
        );
        $content = preg_replace('/[ \t]{2,}/', ' ', (string) $content);

        return trim((string) $content);
    }
}
