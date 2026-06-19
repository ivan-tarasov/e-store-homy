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
use App\Template\TemplateEngine;

final class HomeAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
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
        $cards = '';
        foreach ($this->products->newestWithImages(8) as $product) {
            $cards .= $this->cards->card($product);
        }
        return $this->tpl->render('home', 'new-arrivals', ['cards' => $cards]);
    }

    private function renderBrands(): string
    {
        $items = '';
        foreach ($this->brands->all() as $brand) {
            // ?v=2 busts browsers caching the old text-wordmark placeholders
            $logo = htmlspecialchars($brand->logoPath() . '?v=2', ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars($brand->name, ENT_QUOTES, 'UTF-8');
            $items .= sprintf(
                '<div class="col-4 col-sm-3 col-md-2 brand-cell">'
                . '<img class="brand-logo" src="%s" alt="%s" loading="lazy" />'
                . '</div>',
                $logo,
                $name,
            );
        }
        return $this->tpl->render('home', 'brands', ['items' => $items]);
    }
}
