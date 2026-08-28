<?php

namespace App\Services;

use Illuminate\Support\Str;

class SocialContentPolicy
{
    public static function promptRules(): string
    {
        return <<<'RULES'
CANALES OBLIGATORIOS PARA TODO CONTENIDO GENERADO:
- Trabaja exclusivamente con Facebook, Instagram, TikTok y WhatsApp.
- No menciones ni recomiendes correo electronico, email, e-mail o Gmail.
- Todas las publicaciones, llamados a la accion, objetivos, tareas y metricas deben corresponder a Facebook, Instagram, TikTok o WhatsApp.
- No uses tablas. Presenta la informacion como texto breve o listas simples.
RULES;
    }

    public static function containsExcludedChannel(string $content): bool
    {
        $normalized = Str::lower(Str::ascii($content));

        return preg_match('/gmail|e-?mail|correo(?:s)?(?:\s+electronico(?:s)?)?/', $normalized) === 1;
    }

    public static function sanitize(string $content): string
    {
        $content = preg_replace(
            '/\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b/ui',
            '',
            $content
        );
        $content = preg_replace(
            '/\b(?:Gmail|e-?mail|correos?\s+electr[oó]nicos?|correos?)\b/ui',
            'mensajería de la marca',
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
