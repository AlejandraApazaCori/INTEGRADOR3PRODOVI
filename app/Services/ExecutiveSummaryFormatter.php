<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class ExecutiveSummaryFormatter
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $this->converter = new MarkdownConverter($environment);
    }

    public function sections(string $summary): array
    {
        $summary = str_replace(["\r\n", "\r"], "\n", trim($summary));
        $summary = $this->normalizeInlineNumberedLists($summary);
        $sections = [];
        $currentTitle = null;
        $currentLines = [];

        foreach (explode("\n", $summary) as $line) {
            $line = rtrim($line);

            if ($this->isDecoration($line)) {
                continue;
            }

            $heading = $this->heading($line);
            if ($heading !== null) {
                $this->appendSection($sections, $currentTitle, $currentLines);
                $currentTitle = $heading;
                $currentLines = [];

                continue;
            }

            if ($currentTitle === null && trim($line) !== '') {
                $currentTitle = 'Resumen general de la empresa';
            }

            if ($currentTitle !== null && ! $this->isUnanswered($line)) {
                $currentLines[] = $line;
            }
        }

        $this->appendSection($sections, $currentTitle, $currentLines);

        return $sections;
    }

    private function normalizeInlineNumberedLists(string $markdown): string
    {
        return (string) preg_replace_callback(
            '/^[ \t]*1[.)]\s+.+$/mu',
            fn (array $match) => (string) preg_replace('/\s+(?=\d+[.)]\s+)/u', "\n", $match[0]),
            $markdown
        );
    }

    public function sanitizeEditorHtml(string $html): string
    {
        $dom = $this->editorDom($html);
        $root = $dom->getElementById('executive-editor-root');
        $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'blockquote'];

        $clean = function (DOMNode $parent) use (&$clean, $allowed): void {
            foreach (iterator_to_array($parent->childNodes) as $node) {
                if (! $node instanceof DOMElement) {
                    if ($node->nodeType === XML_COMMENT_NODE) {
                        $parent->removeChild($node);
                    }

                    continue;
                }

                $tag = strtolower($node->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object'], true)) {
                    $parent->removeChild($node);

                    continue;
                }

                $clean($node);
                if (! in_array($tag, $allowed, true)) {
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);

                    continue;
                }

                while ($node->attributes?->length) {
                    $node->removeAttributeNode($node->attributes->item(0));
                }
            }
        };
        $clean($root);

        return collect(iterator_to_array($root->childNodes))
            ->map(fn (DOMNode $node) => $dom->saveHTML($node))
            ->implode('');
    }

    public function markdownFromHtml(string $html): string
    {
        $dom = $this->editorDom($this->sanitizeEditorHtml($html));
        $root = $dom->getElementById('executive-editor-root');

        return trim($this->nodesToMarkdown($root));
    }

    private function editorDom(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="executive-editor-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }

    private function nodesToMarkdown(DOMNode $parent): string
    {
        $markdown = '';
        foreach ($parent->childNodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                $markdown .= preg_replace('/\s+/u', ' ', $node->nodeValue ?? '');

                continue;
            }
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, ['p', 'div'], true)) {
                $markdown .= trim($this->nodesToMarkdown($node))."\n\n";
            } elseif ($tag === 'br') {
                $markdown .= "\n";
            } elseif (in_array($tag, ['strong', 'b'], true)) {
                $markdown .= '**'.trim($this->nodesToMarkdown($node)).'**';
            } elseif (in_array($tag, ['em', 'i'], true)) {
                $markdown .= '*'.trim($this->nodesToMarkdown($node)).'*';
            } elseif (in_array($tag, ['ul', 'ol'], true)) {
                $ordered = $tag === 'ol';
                $position = 1;
                foreach ($node->childNodes as $item) {
                    if ($item instanceof DOMElement && strtolower($item->tagName) === 'li') {
                        $prefix = $ordered ? ($position++).'. ' : '- ';
                        $markdown .= $prefix.trim($this->nodesToMarkdown($item))."\n";
                    }
                }
                $markdown .= "\n";
            } elseif ($tag === 'table') {
                $markdown .= $this->tableToMarkdown($node)."\n\n";
            } elseif ($tag === 'blockquote') {
                $text = trim($this->nodesToMarkdown($node));
                $markdown .= collect(explode("\n", $text))->map(fn ($line) => '> '.$line)->implode("\n")."\n\n";
            } else {
                $markdown .= $this->nodesToMarkdown($node);
            }
        }

        return $markdown;
    }

    private function tableToMarkdown(DOMElement $table): string
    {
        $rows = [];
        foreach ($table->getElementsByTagName('tr') as $row) {
            $cells = [];
            foreach ($row->childNodes as $cell) {
                if ($cell instanceof DOMElement && in_array(strtolower($cell->tagName), ['th', 'td'], true)) {
                    $cells[] = str_replace('|', '\\|', trim(preg_replace('/\s+/u', ' ', $this->nodesToMarkdown($cell))));
                }
            }
            if ($cells !== []) {
                $rows[] = $cells;
            }
        }

        if ($rows === []) {
            return '';
        }

        $columns = max(array_map('count', $rows));
        $rows = array_map(fn ($row) => array_pad($row, $columns, ''), $rows);
        $line = fn ($row) => '| '.implode(' | ', $row).' |';

        return $line($rows[0])."\n".$line(array_fill(0, $columns, '---'))."\n".
            collect(array_slice($rows, 1))->map($line)->implode("\n");
    }

    private function appendSection(array &$sections, ?string $title, array $lines): void
    {
        if (! $title || $this->isMissingInformationSection($title)) {
            return;
        }

        $lines = $this->removeEmptyTables($lines);
        $markdown = trim(implode("\n", $lines));

        if ($markdown === '') {
            return;
        }

        $sections[] = [
            'titulo' => $title,
            'contenido' => $markdown,
            'html' => (string) $this->converter->convert($markdown),
        ];
    }

    private function heading(string $line): ?string
    {
        $line = trim($line);
        $matches = [];

        if (preg_match('/^#{1,6}\s*(?:\d+[.)]?\s*)?(.+)$/u', $line, $matches)
            || preg_match('/^\d+[.)]\s+(?!\*\*)(.+)$/u', $line, $matches)) {
            return $this->cleanTitle($matches[1]);
        }

        return null;
    }

    private function cleanTitle(string $title): string
    {
        $title = preg_replace('/[*_`#]+/u', '', $title);

        return trim(html_entity_decode(strip_tags((string) $title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function isDecoration(string $line): bool
    {
        $line = trim($line);

        return $line === '' ? false : (bool) preg_match('/^(?:-{3,}|_{3,}|\*{3,}|\d+)$/u', $line);
    }

    private function isUnanswered(string $line): bool
    {
        $plain = mb_strtolower($this->ascii(preg_replace('/[*_`|]+/u', ' ', $line)));

        foreach ([
            'dato no proporcionado',
            'informacion no proporcionada',
            'informacion faltante',
            'no se proporciono',
            'no se proporcionaron',
            'no se especifico',
            'no se especificaron',
            'no se describio',
            'no se describieron',
            'no se menciono',
            'no se mencionaron',
            'no se dispone de',
            'ausencia de datos',
            'proximo paso',
            'seccion 11',
        ] as $phrase) {
            if (str_contains($plain, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function isMissingInformationSection(string $title): bool
    {
        $title = mb_strtolower($this->ascii($title));

        return str_contains($title, 'informacion faltante') || str_contains($title, 'datos faltantes');
    }

    private function removeEmptyTables(array $lines): array
    {
        $result = [];
        $count = count($lines);

        for ($index = 0; $index < $count;) {
            if (! str_contains($lines[$index], '|')) {
                $result[] = $lines[$index++];

                continue;
            }

            $table = [];
            while ($index < $count && str_contains($lines[$index], '|')) {
                $table[] = $lines[$index++];
            }

            $dataRows = array_filter($table, fn ($row) => ! preg_match('/^\s*\|?[\s:|-]+\|?\s*$/u', $row));
            if (count($dataRows) >= 2 && count($table) >= 3) {
                array_push($result, ...$table);
            }
        }

        return $result;
    }

    private function ascii(string $value): string
    {
        return strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
    }
}
