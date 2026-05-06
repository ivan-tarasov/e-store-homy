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
use App\Template\TemplateEngine;

final class ProductsAction extends AbstractAdminAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
        private readonly PriceFormatter $price,
        private readonly Slugify $slugify,
        private readonly TemplateEngine $tpl,
    ) {
    }

    protected function auth(): AuthService { return $this->auth; }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $allProducts = $this->products->all();
        $rows = '';
        foreach ($allProducts as $product) {
            $brand = $this->brands->find($product->brandId);
            $category = $this->categories->find($product->categoryId);
            $stockClass = $product->inStock ? 'in' : 'out';
            $stockLabel = $product->inStock ? 'на складе' : 'под заказ';
            $url = $this->slugify->productPath($product->id, $brand?->slug ?? '', $product->name);

            $rows .= $this->tpl->render('admin', 'products-row', [
                'id'          => $product->id,
                'url'         => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                'name'        => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                'category'    => htmlspecialchars($category?->name ?? '—', ENT_QUOTES, 'UTF-8'),
                'brand'       => htmlspecialchars($brand?->name ?? '—', ENT_QUOTES, 'UTF-8'),
                'price'       => $this->price->format($product->price),
                'stock'       => $product->stock,
                'stock_class' => $stockClass,
                'stock_label' => $stockLabel,
                'rating'      => number_format($product->rating, 1),
            ]);
        }

        $body = $this->tpl->render('admin', 'products', [
            'nav'   => AdminNav::render('products'),
            'count' => count($allProducts),
            'rows'  => $rows,
        ]);

        return Response::html($this->layout->render($body, new PageMeta('Товары — админ-панель')));
    }
}
