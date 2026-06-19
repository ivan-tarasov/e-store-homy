<?php

declare(strict_types=1);

namespace App\Action\Home;

use App\Domain\Brand;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use App\Service\ProductCardRenderer;
use App\Support\Lang;
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
            title: Lang::t('home.meta_title'),
            description: Lang::t('home.meta_desc'),
            keywords: Lang::t('home.meta_keywords'),
        );

        return Response::html($this->layout->render($body, $meta));
    }

    private function renderHero(): string
    {
        return sprintf(
            '<section id="hero">'
            . '<div class="container hero-content">'
            . '<div class="hero-text">'
            . '<span class="hero-kicker"><span class="hero-kicker-dot"></span>%s</span>'
            . '<h1 class="hero-title">%s<br/><span class="hero-title-accent">%s</span></h1>'
            . '<p class="hero-subtitle">%s</p>'
            . '<div class="hero-ctas">'
            . '<a class="hero-cta hero-cta-primary" href="/category/">%s</a>'
            . '<a class="hero-cta hero-cta-secondary" href="/about/">%s</a>'
            . '</div></div>'
            . '<div class="hero-products"><img src="/img/banners/hero-products.svg" alt="" /></div>'
            . '</div></section>',
            Lang::t('hero.kicker'),
            Lang::t('hero.title_line1'),
            Lang::t('hero.title_accent'),
            Lang::t('hero.subtitle'),
            Lang::t('hero.cta_catalog'),
            Lang::t('hero.cta_about'),
        );
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
