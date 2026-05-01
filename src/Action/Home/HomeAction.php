<?php

declare(strict_types=1);

namespace App\Action\Home;

use App\Domain\Brand;
use App\Domain\Product;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\PriceFormatter;
use App\Service\Slugify;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class HomeAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly ProductRepository $products,
        private readonly BrandRepository $brands,
        private readonly CategoryRepository $categories,
        private readonly PriceFormatter $price,
        private readonly Slugify $slugify,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $heroBanner = $this->renderHero();
        $newArrivals = $this->renderNewArrivals();
        $brandsBanner = $this->renderBrands();

        $body = $heroBanner . $newArrivals . $brandsBanner;

        $meta = new PageMeta(
            title: 'Homy — каталог бытовой техники и электроники',
            description: 'Демо-витрина магазина бытовой техники: смартфоны, ноутбуки, ТВ, аудио и техника для дома.',
            keywords: 'бытовая техника, электроника, смартфоны, ноутбуки, ТВ, демо',
        );

        return Response::html($this->layout->render($body, $meta));
    }

    private function renderHero(): string
    {
        return <<<'HTML'
<section id="hero" style="background:#fff;">
   <a href="/category/" style="display:block;">
      <img src="/img/banners/hero.svg" alt="Homy demo store" style="width:100%; max-height:380px; display:block;" />
   </a>
</section>
HTML;
    }

    private function renderNewArrivals(): string
    {
        $cards = '';
        foreach ($this->products->newestWithImages(8) as $product) {
            $cards .= $this->renderProductCard($product);
        }

        return <<<HTML
<section class="content-row" style="padding: 2em 0;">
   <div class="container">
      <div class="title-nav"><h1>Новинки каталога</h1></div>
      <div class="row product-grid-holder">
         {$cards}
      </div>
   </div>
</section>
HTML;
    }

    private function renderBrands(): string
    {
        $items = '';
        foreach ($this->brands->all() as $brand) {
            $logo = sprintf('/img/brands/%s.svg', rawurlencode($brand->slug));
            $name = htmlspecialchars($brand->name, ENT_QUOTES, 'UTF-8');
            $items .= sprintf(
                '<div class="col-xs-6 col-sm-4 col-md-2" style="padding:1em;"><div style="background:#fff; border:1px solid #eee; padding:.5em; height:80px; display:flex; align-items:center; justify-content:center;"><img src="%s" alt="%s" style="max-width:100%%; max-height:60px;" /></div></div>',
                $logo,
                $name,
            );
        }

        return <<<HTML
<section class="brands-row" style="padding:2em 0; background:#fafafa;">
   <div class="container">
      <div class="title-nav"><h1>Производители</h1></div>
      <div class="row">{$items}</div>
   </div>
</section>
HTML;
    }

    private function renderProductCard(Product $product): string
    {
        $brand = $this->brands->find($product->brandId);
        $category = $this->categories->find($product->categoryId);
        $url = $this->slugify->productPath(
            $product->id,
            $brand?->slug ?? '',
            $product->name,
        );
        $img = htmlspecialchars($product->mainPhoto() ?? '/img/default-product.svg', ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars(($brand?->name ?? '') . ' ' . $product->name, ENT_QUOTES, 'UTF-8');
        $singular = htmlspecialchars($category?->singular ?? '', ENT_QUOTES, 'UTF-8');
        $priceLabel = $this->price->format($product->price);

        return <<<HTML
<div class="col-xs-6 col-sm-4 col-md-3" style="padding:1em;">
   <div class="product-card" style="background:#fff; border:1px solid #eee; padding:1em; text-align:center;">
      <a href="{$url}"><img src="{$img}" alt="{$name}" style="max-width:100%; height:160px; object-fit:contain;" /></a>
      <div class="brand" style="font-size:.8em; color:#999; text-transform:uppercase;">{$singular}</div>
      <div class="title" style="margin:.5em 0;"><a href="{$url}">{$name}</a></div>
      <div class="price" style="font-weight:700; color:#e57000;">{$priceLabel}</div>
   </div>
</div>
HTML;
    }
}
