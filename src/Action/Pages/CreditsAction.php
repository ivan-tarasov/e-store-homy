<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

/**
 * Image sources & attribution page.
 *
 * This is a non-commercial portfolio/demo project. Product photographs are
 * sourced from Wikimedia Commons under their respective free licenses
 * (CC BY-SA / CC BY / CC0 / public domain); attribution is required by those
 * licenses and is reproduced here. Brand logos are trademarks of their
 * respective owners and are shown for identification purposes only.
 */
final class CreditsAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly string $rootDir,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $photos = $this->load('/public/img/products/credits.json');
        $logos  = $this->load('/public/img/brands/credits.json');

        $body = '<section class="container" style="padding:2em 0;">'
            . '<h1>Источники изображений</h1>'
            . '<p class="text-muted" style="max-width:720px;">'
            . 'Это некоммерческий учебный портфолио-проект. Фотографии товаров взяты '
            . 'из <a href="https://commons.wikimedia.org/" rel="noopener" target="_blank">Wikimedia Commons</a> '
            . 'и используются по их свободным лицензиям (CC BY-SA, CC BY, CC0, общественное достояние). '
            . 'Логотипы брендов являются товарными знаками соответствующих владельцев и приведены '
            . 'исключительно для идентификации товаров.</p>'
            . $this->logosSection($logos)
            . $this->photosSection($photos)
            . '</section>';

        return Response::html($this->layout->render(
            $body,
            new PageMeta('Источники изображений'),
            $this->layout->breadcrumb(null, 'Источники изображений'),
        ));
    }

    /** @param list<array<string,mixed>> $logos */
    private function logosSection(array $logos): string
    {
        if ($logos === []) {
            return '';
        }
        $rows = '';
        foreach ($logos as $c) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars((string) ($c['brand_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                $this->licenseCell($c),
                $this->sourceCell($c),
            );
        }
        return '<h2 style="margin-top:1.5em;">Логотипы брендов</h2>'
            . '<p class="text-muted">Товарные знаки соответствующих владельцев.</p>'
            . '<div class="table-responsive"><table class="table">'
            . '<thead><tr><th>Бренд</th><th>Лицензия</th><th>Источник</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table></div>';
    }

    /** @param list<array<string,mixed>> $photos */
    private function photosSection(array $photos): string
    {
        if ($photos === []) {
            return '';
        }
        // de-duplicate by source file — many products reuse the same upload
        $seen = [];
        $rows = '';
        foreach ($photos as $c) {
            $file = (string) ($c['file'] ?? '');
            if ($file === '' || isset($seen[$file])) {
                continue;
            }
            $seen[$file] = true;
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars((string) ($c['product_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($c['author'] ?? 'Wikimedia Commons'), ENT_QUOTES, 'UTF-8'),
                $this->licenseCell($c),
                $this->sourceCell($c),
            );
        }
        return '<h2 style="margin-top:1.5em;">Фотографии товаров</h2>'
            . '<p class="text-muted">Источник: Wikimedia Commons.</p>'
            . '<div class="table-responsive"><table class="table">'
            . '<thead><tr><th>Товар</th><th>Автор</th><th>Лицензия</th><th>Источник</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table></div>';
    }

    /** @param array<string,mixed> $c */
    private function licenseCell(array $c): string
    {
        $name = htmlspecialchars((string) ($c['license'] ?? 'see source'), ENT_QUOTES, 'UTF-8');
        $url  = $c['license_url'] ?? null;
        if (is_string($url) && $url !== '') {
            return sprintf('<a href="%s" rel="noopener" target="_blank">%s</a>',
                htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $name);
        }
        return $name;
    }

    /** @param array<string,mixed> $c */
    private function sourceCell(array $c): string
    {
        $page = $c['page'] ?? null;
        if (is_string($page) && $page !== '') {
            return sprintf('<a href="%s" rel="noopener" target="_blank">Wikimedia&nbsp;Commons</a>',
                htmlspecialchars($page, ENT_QUOTES, 'UTF-8'));
        }
        return 'Wikimedia Commons';
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function load(string $relPath): array
    {
        $path = $this->rootDir . $relPath;
        if (!is_file($path)) {
            return [];
        }
        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }
}
