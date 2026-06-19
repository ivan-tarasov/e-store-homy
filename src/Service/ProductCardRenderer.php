<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Product;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Support\Lang;

final class ProductCardRenderer
{
    public function __construct(
        private readonly BrandRepository $brands,
        private readonly CategoryRepository $categories,
        private readonly Slugify $slugify,
        private readonly PriceFormatter $price,
    ) {
    }

    public function card(
        Product $product,
        bool $withCart = false,
        bool $withStock = false,
        string $colClass = 'col-6 col-sm-4 col-md-3',
    ): string {
        $brand = $this->brands->find($product->brandId);
        $category = $this->categories->find($product->categoryId);
        $url = $this->slugify->productPath($product->id, $brand?->slug ?? '', $product->name);
        $img = htmlspecialchars($product->mainPhoto() ?? '/img/default-product.svg', ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars(($brand?->name ?? '') . ' ' . $product->name, ENT_QUOTES, 'UTF-8');
        $singular = htmlspecialchars($category?->singular ?? '', ENT_QUOTES, 'UTF-8');
        $priceLabel = $this->price->format($product->price);

        $stockHtml = '';
        if ($withStock) {
            $stock = $product->inStock ? Lang::t('product.in_stock') : Lang::t('product.out_stock');
            $cls = $product->inStock ? 'available' : 'not-available';
            $stockHtml = sprintf(
                '<div class="text-muted" style="font-size:.85em;"><span class="%s">%s</span></div>',
                $cls,
                $stock,
            );
        }

        $cartHtml = '';
        if ($withCart) {
            $cartHtml = sprintf(
                '<form method="post" action="/cart/add" style="margin-top:.5em;">'
                . '<input type="hidden" name="id" value="%d" />'
                . '<button class="le-button small" type="submit">%s</button>'
                . '</form>',
                $product->id,
                Lang::t('product.add_to_cart'),
            );
        }

        return sprintf(
            '<div class="%s" style="padding:.75em;">'
            . '<div class="product-card" style="background:#fff;border:1px solid #eee;padding:1em;">'
            . '<a href="%s"><img src="%s" alt="%s" style="max-width:100%%;height:160px;object-fit:contain;display:block;margin:auto;" /></a>'
            . '<div style="font-size:.8em;color:#999;text-transform:uppercase;margin-top:.5em;">%s</div>'
            . '<div style="margin:.25em 0;"><a href="%s">%s</a></div>'
            . '<div style="font-weight:700;color:#e57000;">%s</div>'
            . '%s%s'
            . '</div></div>',
            htmlspecialchars($colClass, ENT_QUOTES, 'UTF-8'),
            $url, $img, $name,
            $singular,
            $url, $name,
            $priceLabel,
            $stockHtml,
            $cartHtml,
        );
    }

    public function miniCard(Product $product): string
    {
        $brand = $this->brands->find($product->brandId);
        $url = $this->slugify->productPath($product->id, $brand?->slug ?? '', $product->name);
        $img = htmlspecialchars($product->mainPhoto() ?? '/img/default-product.svg', ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars(($brand?->name ?? '') . ' ' . $product->name, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<div class="col-6 col-sm-3" style="padding:.75em;">'
            . '<a href="%s"><img src="%s" alt="%s" style="max-width:100%%;height:140px;object-fit:contain;" /></a>'
            . '<div><a href="%s">%s</a></div>'
            . '<div style="color:#e57000;font-weight:700;">%s</div>'
            . '</div>',
            $url, $img, $name,
            $url, $name,
            $this->price->format($product->price),
        );
    }
}
