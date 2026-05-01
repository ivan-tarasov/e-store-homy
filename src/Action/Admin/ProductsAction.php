<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\AuthService;
use App\Service\PriceFormatter;
use App\Service\Slugify;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class ProductsAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
        private readonly PriceFormatter $price,
        private readonly Slugify $slugify,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if (!$this->auth->isAdmin()) {
            return Response::redirect('/login/');
        }

        $rows = '';
        foreach ($this->products->all() as $product) {
            $brand = $this->brands->find($product->brandId);
            $category = $this->categories->find($product->categoryId);
            $stockClass = $product->inStock ? 'in' : 'out';
            $stockLabel = $product->inStock ? 'на складе' : 'под заказ';
            $url = $this->slugify->productPath($product->id, $brand?->slug ?? '', $product->name);

            $rows .= sprintf(
                '<tr>'
                . '<td>%d</td>'
                . '<td><a href="%s" target="_blank">%s</a></td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td class="text-right">%s</td>'
                . '<td class="text-center">%d</td>'
                . '<td><span class="stock-pill stock-%s">%s</span></td>'
                . '<td class="text-right">%.1f ★</td>'
                . '</tr>',
                $product->id,
                htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($category?->name ?? '—', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($brand?->name ?? '—', ENT_QUOTES, 'UTF-8'),
                $this->price->format($product->price),
                $product->stock,
                $stockClass,
                $stockLabel,
                $product->rating,
            );
        }

        $body = sprintf(
            '<section class="container admin-page" style="padding:2em 0;">'
            . '<div class="row"><div class="col-md-3">%s</div><div class="col-md-9">'
            . '<h1>Товары (%d)</h1>'
            . '<table class="table"><thead><tr>'
            . '<th>ID</th><th>Название</th><th>Категория</th><th>Бренд</th>'
            . '<th class="text-right">Цена</th><th class="text-center">Запас</th><th>Наличие</th><th class="text-right">Рейтинг</th>'
            . '</tr></thead><tbody>%s</tbody></table>'
            . '</div></div></section>',
            AdminNav::render('products'),
            count($this->products->all()),
            $rows,
        );

        return Response::html($this->layout->render($body, new PageMeta('Товары — админ-панель')));
    }
}
