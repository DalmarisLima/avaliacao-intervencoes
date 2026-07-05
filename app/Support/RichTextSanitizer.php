<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

class RichTextSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ol><ul><li><a>';

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = self::sanitizeLinks(trim($clean));

        if (in_array($clean, ['<p><br></p>', '<p></p>', '<br>'], true)) {
            return '';
        }

        return $clean;
    }

    public static function plain(?string $html): string
    {
        $text = strip_tags(self::clean($html));

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    public static function display(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        if (str_contains($content, '<')) {
            return self::clean($content);
        }

        return nl2br(e($content), false);
    }

    public static function normalizeHref(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^mailto:/i', $value)) {
            return 'mailto:'.self::normalizeEmailAddress(substr($value, 7));
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:'.$value;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        return 'https://'.$value;
    }

    private static function sanitizeLinks(string $html): string
    {
        if (! str_contains(strtolower($html), '<a')) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);
        $doc = new DOMDocument;
        $doc->loadHTML(
            '<?xml encoding="utf-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//a') ?: [] as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $href = self::normalizeHref($anchor->getAttribute('href'));

            if (! self::isAllowedHref($href)) {
                self::unwrapNode($anchor);

                continue;
            }

            $anchor->setAttribute('href', $href);
            if (str_starts_with(strtolower($href), 'mailto:')) {
                $anchor->removeAttribute('target');
            } else {
                $anchor->setAttribute('target', '_blank');
                $anchor->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $div = $doc->getElementsByTagName('div')->item(0);
        if ($div === null) {
            return $html;
        }

        $inner = '';
        foreach ($div->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }

        return trim($inner);
    }

    private static function isAllowedHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        if (str_starts_with(strtolower($href), 'mailto:')) {
            $email = substr($href, 7);

            return self::isValidEmail($email);
        }

        return (bool) filter_var($href, FILTER_VALIDATE_URL);
    }

    private static function isValidEmail(string $email): bool
    {
        $email = trim($email);

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private static function normalizeEmailAddress(string $email): string
    {
        return trim($email);
    }

    private static function unwrapNode(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }
}
