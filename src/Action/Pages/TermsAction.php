<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class TermsAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $body = '<section class="container" style="padding:2em 0;">'
            . '<h1>Оплата и доставка</h1>'
            . '<p>Это демо-страница условий продажи. В реальном магазине здесь были бы условия оплаты, способы и сроки доставки, гарантии и контакты.</p>'
            . '<h3>Способы оплаты</h3>'
            . '<ul><li>Наличными при получении</li><li>Банковской картой</li><li>Безналичный расчёт</li></ul>'
            . '<h3>Доставка</h3>'
            . '<p>Бесплатная доставка по городу при сумме заказа от 5 000 ₽.</p>'
            . '</section>';

        return Response::html($this->layout->render(
            $body,
            new PageMeta('Оплата и доставка'),
            $this->layout->breadcrumb(null, 'Оплата и доставка'),
        ));
    }
}
