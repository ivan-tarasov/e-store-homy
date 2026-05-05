<?php

declare(strict_types=1);

namespace App\Action\Home;

use App\Domain\Brand;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use App\Service\ProductCardRenderer;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class HomeAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly ProductRepository $products,
        private readonly BrandRepository $brands,
        private readonly ProductCardRenderer $cards,
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
<section id="hero">
   <div class="container hero-content">
      <div class="hero-text">
         <span class="hero-kicker"><span class="hero-kicker-dot"></span>PORTFOLIO DEMO</span>
         <h1 class="hero-title">
            Бытовая техника,<br/>
            <span class="hero-title-accent">собранная для демо</span>
         </h1>
         <p class="hero-subtitle">PHP 8 · файловое хранилище · реальные фото с Wikimedia Commons</p>
         <div class="hero-ctas">
            <a class="hero-cta hero-cta-primary" href="/category/">Открыть каталог →</a>
            <a class="hero-cta hero-cta-secondary" href="/about/">О проекте</a>
         </div>
      </div>
      <div class="hero-products">
         <img src="/img/banners/hero-products.svg" alt="" />
      </div>
   </div>
</section>
HTML;
    }

    private function renderNewArrivals(): string
    {
        $cardHtml = '';
        foreach ($this->products->newestWithImages(8) as $product) {
            $cardHtml .= $this->cards->card($product);
        }

        return <<<HTML
<section class="content-row" style="padding: 2em 0;">
   <div class="container">
      <div class="title-nav"><h1>Новинки каталога</h1></div>
      <div class="row product-grid-holder">
         {$cardHtml}
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
                '<div class="col-6 col-sm-4 col-md-2" style="padding:1em;"><div style="background:#fff; border:1px solid #eee; padding:.5em; height:80px; display:flex; align-items:center; justify-content:center;"><img src="%s" alt="%s" style="max-width:100%%; max-height:60px;" /></div></div>',
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
}
