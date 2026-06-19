<?php

declare(strict_types=1);

namespace App\Action\Product;

use App\Action\Errors\NotFoundAction;
use App\Domain\Product;
use App\Domain\Review;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Service\CartService;
use App\Service\PriceFormatter;
use App\Service\ProductCardRenderer;
use App\Service\RussianLocale;
use App\Support\Lang;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class ProductShowAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
        private readonly ReviewRepository $reviews,
        private readonly PriceFormatter $price,
        private readonly ProductCardRenderer $cards,
        private readonly RussianLocale $locale,
        private readonly CartService $cart,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $idslug = $vars['idslug'] ?? '';
        $idPart = explode('-', $idslug, 2)[0] ?? '';
        if (!ctype_digit($idPart)) {
            return (new NotFoundAction($this->layout))($request, $vars);
        }

        $product = $this->products->find((int) $idPart);
        if ($product === null) {
            return (new NotFoundAction($this->layout))($request, $vars);
        }

        $brand = $this->brands->find($product->brandId);
        $category = $this->categories->find($product->categoryId);

        $properties = $this->renderProperties($product);
        $gallery = $this->renderGallery($product);
        $rawReviews = $this->reviews->forProduct($product->id);
        $reviews = $this->renderReviews($rawReviews);
        $similar = $this->renderSimilar($product);

        $stock = $product->inStock ? Lang::t('product.in_stock') : Lang::t('product.out_stock');
        $stockClass = $product->inStock ? 'available' : 'not-available';
        $title = ($category?->singular ?? '') . ' '
            . htmlspecialchars($brand?->name ?? '', ENT_QUOTES, 'UTF-8') . ' '
            . htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8');

        $cartButton = $this->cart->has($product->id)
            ? '<a class="le-button huge incart" href="/cart/">' . Lang::t('product.in_cart') . '</a>'
            : sprintf(
                '<form method="post" action="/cart/add"><input type="hidden" name="id" value="%d" /><button class="le-button huge" type="submit">%s</button></form>',
                $product->id,
                Lang::t('product.add_to_cart'),
            );

        $body = $this->tpl->render('product', 'single-product', [
            'promo_banner' => '',
            'gallery' => $gallery,
            'available_bool' => $product->inStock ? '' : 'not-',
            'prod_axistence' => $stock,
            'item_name_hide' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
            'item_name' => '<h1>' . $title . '</h1>',
            'rating' => $this->renderRating($product->rating),
            '1c_id' => (string) $product->id,
            'brand' => htmlspecialchars($brand?->name ?? '', ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'),
            'price_current_clear' => (string) $product->price,
            'price_current' => $this->price->format($product->price),
            'no_exist_price' => '',
            'no_exist_note' => '',
            'to_cart_button' => $cartButton,
            'to_cart_button_clc' => '',
            'reviews_count' => (string) count($rawReviews),
            'opinions' => $reviews,
            'properties' => $properties,
            'same_category' => $similar,
            'seo_text' => '',
            'categoryPath' => htmlspecialchars($category?->name ?? '', ENT_QUOTES, 'UTF-8'),
        ]);

        $meta = new PageMeta(
            title: ($brand?->name ?? '') . ' ' . $product->name,
            description: mb_substr(strip_tags($product->description), 0, 200),
        );

        return Response::html($this->layout->render($body, $meta, $this->layout->breadcrumb($product->categoryId, $product->name)));
    }

    private function renderGallery(Product $product): string
    {
        if ($product->photos === []) {
            return '<div class="text-center" style="padding:2em;"><img src="/img/default-product.svg" alt="" style="max-width:100%;" /></div>';
        }

        $alt = htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8');

        $slides = '';
        $thumbs = '';
        foreach ($product->photos as $i => $photo) {
            $src = htmlspecialchars($photo, ENT_QUOTES, 'UTF-8');
            $slides .= sprintf(
                '<div class="swiper-slide product-gallery-slide" style="text-align:center;background:#fafafa;"><img src="%s" alt="%s" style="max-height:420px;max-width:100%%;display:inline-block;" /></div>',
                $src,
                $alt,
            );
            $cls = $i === 0 ? 'product-gallery-thumb is-active' : 'product-gallery-thumb';
            $thumbs .= sprintf(
                '<a href="#" class="%s" data-index="%d" style="display:inline-block;width:64px;height:64px;margin:.25em;border:2px solid transparent;background:#fff;overflow:hidden;"><img src="%s" alt="" style="width:100%%;height:100%%;object-fit:cover;" /></a>',
                $cls,
                $i,
                $src,
            );
        }

        return sprintf(
            '<div id="product-gallery">'
            . '<div id="product-gallery-main" class="swiper">'
            . '<div class="swiper-wrapper">%s</div>'
            . '<div class="swiper-button-prev"></div>'
            . '<div class="swiper-button-next"></div>'
            . '</div>'
            . '<div class="product-gallery-thumbs" style="text-align:center;margin-top:1em;">%s</div>'
            . '</div>',
            $slides,
            $thumbs,
        );
    }

    private function renderProperties(Product $product): string
    {
        if ($product->properties === []) {
            return '<p class="text-muted">' . Lang::t('product.specs_none') . '</p>';
        }
        $rows = '';
        foreach ($product->properties as $name => $value) {
            $rows .= sprintf(
                '<div class="row prop-list"><div class="col-md-6"><label>%s</label></div><div class="col-md-6">%s</div></div>',
                htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
            );
        }
        return sprintf(
            '<div class="row" style="margin-bottom:2em;"><div class="col-md-12"><h3 class="lead">%s</h3></div><div class="col-md-12">%s</div></div>',
            Lang::t('product.specs_heading'),
            $rows,
        );
    }

    /** @param list<Review> $reviews */
    private function renderReviews(array $reviews): string
    {
        if ($reviews === []) {
            return '<div class="card card-body bg-light">' . Lang::t('product.reviews_none') . '</div>';
        }
        $html = '';
        foreach ($reviews as $review) {
            $html .= sprintf(
                '<div class="opinion" style="border-bottom:1px solid #eee;padding:1em 0;">'
                . '<div><strong>%s</strong> <small class="text-muted">%s</small></div>'
                . '<div class="rating">%s</div>'
                . '<p>%s</p>'
                . '%s%s'
                . '</div>',
                htmlspecialchars($review->author, ENT_QUOTES, 'UTF-8'),
                $this->locale->formatDate($review->createdAt),
                Lang::t('product.review_grade', ['n' => $review->grade]),
                nl2br(htmlspecialchars($review->comment, ENT_QUOTES, 'UTF-8')),
                $review->pros !== null
                    ? '<p><strong>' . Lang::t('product.review_pros') . '</strong> ' . htmlspecialchars($review->pros, ENT_QUOTES, 'UTF-8') . '</p>'
                    : '',
                $review->cons !== null
                    ? '<p><strong>' . Lang::t('product.review_cons') . '</strong> ' . htmlspecialchars($review->cons, ENT_QUOTES, 'UTF-8') . '</p>'
                    : '',
            );
        }
        return $html;
    }

    private function renderRating(float $rating): string
    {
        $full = (int) floor($rating);
        $empty = 5 - $full;
        return '<span class="rating-stars">'
            . str_repeat('★', $full)
            . str_repeat('☆', $empty)
            . sprintf(' <small class="text-muted">(%.1f)</small></span>', $rating);
    }

    private function renderSimilar(Product $product): string
    {
        $items = $this->products->inSameCategory($product->categoryId, $product->id, 4);
        if ($items === []) {
            return '';
        }
        $html = '';
        foreach ($items as $item) {
            $html .= $this->cards->miniCard($item);
        }
        return '<section class="container" style="padding:2em 0;"><h3>' . Lang::t('product.same_category') . '</h3><div class="row">' . $html . '</div></section>';
    }
}
