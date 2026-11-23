<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\Pure;

/**
 * Perender blok konten manifest menjadi HTML.
 *
 * Blok menjaga konten tetap berupa data (bukan 35 berkas HTML nyaris kembar),
 * sementara tampilan tetap seragam untuk seluruh halaman indikator.
 *
 * Tautan yang belum ada berkasnya: sel/teks berawalan "!" menjadi
 * <a href="#" data-pa-kosong="1">…</a> sehingga auditor bisa menghitungnya.
 */
final class Blocks
{
    /**
     * Catatan operator di manifest adalah panduan penyuntingan, bukan isi
     * halaman publik. Matikan agar tidak ikut tercetak ke artikel seed.
     */
    public static bool $tampilkanCatatan = false;

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @param array<string, string> $tokens
     * @param array<string, string> $pre
     */
    public static function render(array $blocks, array $tokens = [], array $pre = []): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $html .= self::one($block, $tokens, $pre);
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     * @param array<string, string> $tokens
     * @param array<string, string> $pre
     */
    private static function one(array $b, array $tokens, array $pre): string
    {
        return match ($b['t'] ?? '') {
            'intro'   => '<p class="pa-intro">' . self::inline((string) $b['text'], $tokens) . "</p>\n",
            'note'    => self::$tampilkanCatatan
                         ? '<div class="pa-note" role="note"><span class="pa-note__label">Catatan operator</span><p>'
                         . self::inline((string) $b['text'], $tokens) . "</p></div>\n"
                         : '',
            'quote'   => '<blockquote class="pa-quote"><p>' . self::inline((string) $b['text'], $tokens) . "</p></blockquote>\n",
            'list'    => self::listing($b['items'] ?? [], $tokens),
            'table'   => self::table($b, $tokens),
            'files'   => self::files($b, $tokens),
            'cta'     => self::cta($b['items'] ?? [], $tokens),
            'cards'   => self::cards($b['items'] ?? [], $tokens),
            'image'   => self::image($b, $tokens),
            'scores'  => self::scores($b['items'] ?? [], $tokens),
            'steps'   => self::steps($b['items'] ?? [], $tokens),
            'module'  => '<div class="pa-module-slot">{loadposition ' . self::e((string) ($b['position'] ?? '')) . "}</div>\n",
            'sinova-table' => $pre['sinova-table'] ?? '',
            default   => '',
        };
    }

    /** @param array<string, string> $tokens */
    public static function inline(string $text, array $tokens = []): string
    {
        $safe = self::e(strtr($text, $tokens));

        return (string) preg_replace(
            '/!\s*([A-Za-z][^<]{1,60}?)(?=[.;]|$)/u',
            '<a href="#" data-pa-kosong="1">$1</a>',
            $safe
        );
    }

    public static function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** @param array<int, string> $items */
    private static function listing(array $items, array $tokens): string
    {
        if ($items === []) {
            return '';
        }

        $html = "<ul class=\"pa-list\">\n";
        foreach ($items as $item) {
            $html .= '  <li>' . self::inline((string) $item, $tokens) . "</li>\n";
        }

        return $html . "</ul>\n";
    }

    /** @param array<string, mixed> $b */
    private static function table(array $b, array $tokens): string
    {
        $head = $b['head'] ?? [];
        $rows = $b['rows'] ?? [];

        if ($head === [] || $rows === []) {
            return '';
        }

        $html = "<div class=\"pa-table-wrap\">\n<table class=\"pa-table\">\n  <thead><tr>";
        foreach ($head as $cell) {
            $html .= '<th scope="col">' . self::e((string) $cell) . '</th>';
        }
        $html .= "</tr></thead>\n  <tbody>\n";

        foreach ($rows as $row) {
            $html .= "    <tr>";
            foreach ($row as $cell) {
                $html .= '<td>' . self::cell((string) $cell, $tokens) . '</td>';
            }
            $html .= "</tr>\n";
        }

        return $html . "  </tbody>\n</table>\n</div>\n";
    }

    /** @param array<string, mixed> $b */
    private static function files(array $b, array $tokens): string
    {
        $years = $b['years'] ?? [];
        $doc   = (string) ($b['doc'] ?? 'Dokumen');

        if ($years === []) {
            return '';
        }

        $html = "<div class=\"pa-table-wrap\">\n<table class=\"pa-table pa-table--dokumen\">\n"
            . "  <caption>" . self::e($doc) . "</caption>\n"
            . "  <thead><tr><th scope=\"col\">Tahun</th><th scope=\"col\">Dokumen</th><th scope=\"col\">Tautan</th></tr></thead>\n  <tbody>\n";

        foreach ($years as $year) {
            $html .= '    <tr><td>' . self::e((string) $year) . '</td><td>' . self::e($doc)
                . '</td><td><a href="#" data-pa-kosong="1">Belum diunggah</a></td></tr>' . "\n";
        }

        return $html . "  </tbody>\n</table>\n</div>\n";
    }

    /** @param array<int, array<string, string>> $items */
    private static function cta(array $items, array $tokens): string
    {
        if ($items === []) {
            return '';
        }

        $html = "<div class=\"pa-cta\">\n";
        foreach ($items as $item) {
            $label = self::e(strtr((string) ($item['label'] ?? ''), $tokens));
            $href  = (string) ($item['href'] ?? '#');

            if ($href === '#') {
                $html .= '  <a class="pa-btn pa-btn--primary" href="#" data-pa-kosong="1">' . $label . "</a>\n";
            } elseif (str_starts_with($href, 'http')) {
                $html .= '  <a class="pa-btn pa-btn--primary" href="' . self::e($href)
                    . '" target="_blank" rel="noopener noreferrer">' . $label . "</a>\n";
            } else {
                $html .= '  <a class="pa-btn pa-btn--primary" href="' . self::e($href) . '">' . $label . "</a>\n";
            }
        }

        return $html . "</div>\n";
    }

    /** @param array<int, array<string, string>> $items */
    private static function cards(array $items, array $tokens): string
    {
        if ($items === []) {
            return '';
        }

        $html = "<ul class=\"pa-cards\">\n";
        foreach ($items as $item) {
            $icon  = self::e((string) ($item['icon'] ?? ''));
            $title = self::inline((string) ($item['title'] ?? ''), $tokens);
            $text  = self::inline((string) ($item['text'] ?? ''), $tokens);
            $href  = (string) ($item['href'] ?? '');

            $base = $tokens['{{BASE}}'] ?? '/';
            $inner = ($icon !== '' ? '<img class="pa-card__icon" src="' . self::e($base) . 'images/ikon/' . $icon . '.svg" alt="" width="36" height="36">' : '')
                . '<span class="pa-card__title">' . $title . '</span>'
                . ($text !== '' ? '<span class="pa-card__text">' . $text . '</span>' : '');

            $html .= '  <li class="pa-card">'
                . ($href !== '' ? '<a href="' . self::e($href) . '">' . $inner . '</a>' : $inner)
                . "</li>\n";
        }

        return $html . "</ul>\n";
    }

    /** @param array<string, mixed> $b */
    private static function image(array $b, array $tokens): string
    {
        $base = $tokens['{{BASE}}'] ?? '/';
        $src  = str_replace('{{BASE}}', $base, (string) ($b['src'] ?? ''));
        $alt  = self::e(strtr((string) ($b['alt'] ?? ''), $tokens));

        if ($src === '') {
            return '';
        }

        return '<figure class="pa-figure"><img src="' . self::e($src) . '" alt="' . $alt
            . '" loading="lazy" decoding="async"><figcaption>' . $alt . "</figcaption></figure>\n";
    }

    /** @param array<int, array<string, string>> $items */
    private static function scores(array $items, array $tokens): string
    {
        if ($items === []) {
            return '';
        }

        $html = "<div class=\"pa-scores\">\n";
        foreach ($items as $item) {
            $html .= '  <div class="pa-score"><span class="pa-score__label">'
                . self::inline((string) ($item['label'] ?? ''), $tokens)
                . '</span><strong class="pa-score__value">' . self::e((string) ($item['value'] ?? '—'))
                . "</strong></div>\n";
        }

        return $html . "</div>\n";
    }

    /** @param array<int, array<string, string>> $items */
    private static function steps(array $items, array $tokens): string
    {
        if ($items === []) {
            return '';
        }

        $html = "<ol class=\"pa-steps\">\n";
        foreach ($items as $item) {
            $html .= '  <li><h3 class="pa-steps__title">' . self::inline((string) ($item['title'] ?? ''), $tokens)
                . '</h3><p>' . self::inline((string) ($item['text'] ?? ''), $tokens) . "</p></li>\n";
        }

        return $html . "</ol>\n";
    }

    /** @param array<string, string> $tokens */
    private static function cell(string $cell, array $tokens): string
    {
        if (str_starts_with($cell, '!')) {
            return '<a href="#" data-pa-kosong="1">' . self::e(strtr(substr($cell, 1), $tokens)) . '</a>';
        }

        return self::inline($cell, $tokens);
    }
}
