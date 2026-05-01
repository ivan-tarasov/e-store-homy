<?php

declare(strict_types=1);

namespace App\Action\Cart;

use App\Http\Request;
use App\Http\Response;
use App\Service\CartService;
use App\Service\PriceFormatter;
use App\Service\Slugify;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class CartAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly CartService $cart,
        private readonly PriceFormatter $price,
        private readonly Slugify $slugify,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($this->cart->isEmpty()) {
            $body = '<section class="container" style="padding:3em 0;text-align:center;">'
                . '<h1>Корзина пуста</h1>'
                . '<p>Добавьте товары из <a href="/category/">каталога</a>.</p>'
                . '</section>';
            return Response::html($this->layout->render($body, new PageMeta('Корзина пуста')));
        }

        $rows = '';
        foreach ($this->cart->lineItems() as $line) {
            $product = $line['product'];
            $rows .= sprintf(
                '<tr>'
                . '<td><a href="%s">%s</a></td>'
                . '<td class="text-right">%s</td>'
                . '<td class="text-center">'
                . '<form method="post" action="/cart/update" style="display:inline-flex;gap:.25em;">'
                . '<input type="hidden" name="id" value="%d" />'
                . '<input type="number" name="qty" value="%d" min="1" max="99" style="width:4em;" />'
                . '<button class="le-button small" type="submit">Обновить</button></form>'
                . '</td>'
                . '<td class="text-right">%s</td>'
                . '<td><form method="post" action="/cart/remove"><input type="hidden" name="id" value="%d" /><button class="le-button small" type="submit">×</button></form></td>'
                . '</tr>',
                $this->productUrl($product->id, $product->name),
                htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                $this->price->format($product->price),
                $product->id,
                $line['qty'],
                $this->price->format($line['line_total']),
                $product->id,
            );
        }

        $total = $this->price->format($this->cart->totalAmount());

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>Корзина</h1>'
            . '<table class="table" style="width:100%%;"><thead><tr>'
            . '<th>Товар</th><th class="text-right">Цена</th><th class="text-center">Кол-во</th><th class="text-right">Сумма</th><th></th>'
            . '</tr></thead><tbody>%s</tbody>'
            . '<tfoot><tr><th colspan="3" class="text-right">Итого</th><th class="text-right">%s</th><th></th></tr></tfoot>'
            . '</table>'
            . '<p class="text-right" style="margin-top:2em;"><a class="le-button huge" href="/checkout/">Оформить заказ</a></p>'
            . '</section>',
            $rows,
            $total,
        );

        return Response::html($this->layout->render($body, new PageMeta('Корзина'), $this->layout->breadcrumb(null, 'Корзина')));
    }

    private function productUrl(int $id, string $name): string
    {
        return $this->slugify->productPath($id, '', $name);
    }
}
