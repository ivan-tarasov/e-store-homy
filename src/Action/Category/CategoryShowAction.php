<?php

declare(strict_types=1);

namespace App\Action\Category;

use App\Action\Errors\NotFoundAction;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ProductCardRenderer;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class CategoryShowAction
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
        private readonly ProductRepository $products,
        private readonly ProductCardRenderer $cards,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $category = $this->categories->findBySlug($vars['slug'] ?? '');
        if ($category === null) {
            return (new NotFoundAction($this->layout))($request, $vars);
        }

        $brandSlug = $vars['brand'] ?? null;
        $brand = $brandSlug !== null ? $this->brands->findBySlug($brandSlug) : null;

        $page = max(1, $request->int('page', 1));
        $sort = $request->input('sort', 'default') ?? 'default';
        $offset = ($page - 1) * self::PER_PAGE;

        $items = $this->products->inCategory(
            categoryId: $category->id,
            brandId: $brand?->id,
            sort: $sort,
            offset: $offset,
            limit: self::PER_PAGE,
        );
        $total = $this->products->countInCategory($category->id, $brand?->id);

        $facets = $this->products->brandFacets($category->id);
        $sidebar = $this->renderBrandSidebar($facets, $category->slug, $brand?->slug);
        $sortBar = $this->renderSortBar($category->slug, $brandSlug, $sort, $total);
        $grid = $this->renderProductGrid($items);
        $pagination = $this->renderPagination($category->slug, $brandSlug, $page, $total, $sort);

        $heading = htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8')
            . ($brand !== null ? ' — ' . htmlspecialchars($brand->name, ENT_QUOTES, 'UTF-8') : '');

        $body = $this->tpl->render('category', 'show', [
            'sidebar'    => $sidebar,
            'heading'    => $heading,
            'sort_bar'   => $sortBar,
            'grid'       => $grid,
            'pagination' => $pagination,
        ]);

        $meta = new PageMeta(
            title: $category->name . ' — Homy',
            description: $category->description ?? ('Категория ' . $category->name),
        );

        return Response::html($this->layout->render($body, $meta, $this->layout->breadcrumb($category->id)));
    }

    /**
     * @param list<array{brand_id: int, count: int}> $facets
     */
    private function renderBrandSidebar(array $facets, string $catSlug, ?string $activeBrandSlug): string
    {
        if ($facets === []) {
            return '';
        }

        $items = '';
        foreach ($facets as $f) {
            $brand = $this->brands->find($f['brand_id']);
            if ($brand === null) {
                continue;
            }
            $active = $activeBrandSlug === $brand->slug ? ' class="active"' : '';
            $items .= sprintf(
                '<li%s><a href="/category/%s/brand/%s/">%s <span class="text-muted">(%d)</span></a></li>',
                $active,
                rawurlencode($catSlug),
                rawurlencode($brand->slug),
                htmlspecialchars($brand->name, ENT_QUOTES, 'UTF-8'),
                $f['count'],
            );
        }

        $reset = $activeBrandSlug !== null
            ? sprintf('<p><a href="/category/%s/">Сбросить фильтр</a></p>', rawurlencode($catSlug))
            : '';

        return sprintf(
            '<aside class="sidebar"><h3>Производитель</h3><ul class="list-unstyled">%s</ul>%s</aside>',
            $items,
            $reset,
        );
    }

    private function renderSortBar(string $catSlug, ?string $brandSlug, string $activeSort, int $total): string
    {
        $base = $brandSlug !== null
            ? sprintf('/category/%s/brand/%s/', rawurlencode($catSlug), rawurlencode($brandSlug))
            : sprintf('/category/%s/', rawurlencode($catSlug));

        $options = [
            'default'    => 'По умолчанию',
            'price-asc'  => 'Цена ↑',
            'price-desc' => 'Цена ↓',
            'rating-desc' => 'По рейтингу',
        ];

        $select = '';
        foreach ($options as $value => $label) {
            $selected = $value === $activeSort ? ' selected' : '';
            $select .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                $selected,
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            );
        }

        return sprintf(
            '<div class="sort-bar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1em;">'
            . '<span class="text-muted" style="font-size:.9em;">%d товар(ов)</span>'
            . '<form method="get" action="%s" style="display:flex;align-items:center;gap:.5em;">'
            . '<label style="font-size:.9em;margin:0;">Сортировка:</label>'
            . '<select name="sort" class="form-control" style="width:auto;height:2em;padding:0 .5em;font-size:.9em;" onchange="this.form.submit()">%s</select>'
            . '</form>'
            . '</div>',
            $total,
            htmlspecialchars($base, ENT_QUOTES, 'UTF-8'),
            $select,
        );
    }

    /** @param list<Product> $items */
    private function renderProductGrid(array $items): string
    {
        if ($items === []) {
            return '<p class="text-muted">В этой категории пока нет товаров.</p>';
        }

        $html = '';
        foreach ($items as $product) {
            $html .= $this->cards->card($product, withCart: true, withStock: true, colClass: 'col-12 col-sm-6 col-md-4');
        }

        return '<div class="row">' . $html . '</div>';
    }

    private function renderPagination(string $catSlug, ?string $brandSlug, int $current, int $total, string $sort): string
    {
        $pages = (int) ceil($total / self::PER_PAGE);
        if ($pages <= 1) {
            return '';
        }

        $base = $brandSlug !== null
            ? sprintf('/category/%s/brand/%s/', rawurlencode($catSlug), rawurlencode($brandSlug))
            : sprintf('/category/%s/', rawurlencode($catSlug));

        $html = '<ul class="pagination" style="margin-top:2em;">';
        for ($i = 1; $i <= $pages; $i++) {
            $cls = $i === $current ? ' class="active"' : '';
            $url = $base . '?page=' . $i . ($sort !== 'default' ? '&sort=' . urlencode($sort) : '');
            $html .= sprintf('<li%s><a href="%s">%d</a></li>', $cls, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $i);
        }
        $html .= '</ul>';
        return $html;
    }
}
