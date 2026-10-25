<?php

namespace App\Support;

final class RichText
{
    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return null;
        }

        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote><a>');
        $html = preg_replace('/\s(?:on\w+|style|class|id|target)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace_callback('/<a\b([^>]*)>/i', function (array $matches): string {
            if (! preg_match('/href\s*=\s*("|\')(.*?)\1/i', $matches[1], $href)) {
                return '<a>';
            }

            $url = trim(html_entity_decode($href[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (! preg_match('#^(https?://|mailto:)#i', $url)) {
                return '<a>';
            }

            return '<a href="'.e($url).'" rel="noopener noreferrer">';
        }, $html) ?? '';

        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = str_replace("\u{00A0}", '', $text);
        if ($text === '') {
            return null;
        }

        return $html;
    }
}
