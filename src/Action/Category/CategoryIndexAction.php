<?php

declare(strict_types=1);

namespace App\Action\Category;

use App\Http\Request;
use App\Http\Response;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\RussianLocale;
use App\Support\Lang;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class CategoryIndexAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly CategoryRepository $categories,
        private readonly ProductRepository $products,
        private readonly RussianLocale $locale,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $banners = '';
        foreach ($this->categories->rootCategories() as $cat) {
            $banners .= sprintf(
                '<div class="col-12 col-sm-6 col-md-4" style="padding:1em;">'
                . '<a href="/category/%s/" class="banner-card" style="display:block; padding:2em; background:#fff; border:1px solid #eee; text-align:center;">'
                . '<h3>%s</h3>'
                . '<p class="text-muted">%s</p>'
                . '</a></div>',
                rawurlencode($cat->slug),
                htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($cat->description ?? '', ENT_QUOTES, 'UTF-8'),
            );
        }

        $count = count($this->products->all());
        $word = Lang::isEnglish()
            ? Lang::t($count === 1 ? 'word.items.one' : 'word.items.few')
            : $this->locale->plural($count, [
                Lang::t('word.items.one'),
                Lang::t('word.items.few'),
                Lang::t('word.items.many'),
            ]);

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>%s</h1>'
            . '<p>%s</p>'
            . '<div class="row">%s</div>'
            . '</section>',
            Lang::t('catalog.title'),
            Lang::t('catalog.intro', ['count' => $count, 'word' => $word]),
            $banners,
        );

        $meta = new PageMeta(
            title: Lang::t('catalog.meta_title'),
            description: Lang::t('catalog.meta_desc'),
        );

        return Response::html($this->layout->render($body, $meta, $this->layout->breadcrumb(null)));
    }
}
