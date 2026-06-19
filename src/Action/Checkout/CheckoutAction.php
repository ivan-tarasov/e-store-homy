<?php

declare(strict_types=1);

namespace App\Action\Checkout;

use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;
use App\Service\CartService;
use App\Service\PriceFormatter;
use App\Service\Slugify;
use App\Support\Lang;
use App\Support\Session;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class CheckoutAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly CartService $cart,
        private readonly PriceFormatter $price,
        private readonly Slugify $slugify,
        private readonly AuthService $auth,
        private readonly Session $session,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($this->cart->isEmpty()) {
            return Response::redirect('/cart/');
        }

        $error = $this->session->flash('checkout_error');
        $errorBox = $error !== null
            ? sprintf('<div class="alert alert-danger">%s</div>', htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8'))
            : '';

        $rows = '';
        foreach ($this->cart->lineItems() as $line) {
            $product = $line['product'];
            $rows .= sprintf(
                '<tr><td>%s</td><td class="text-end">%s × %d</td><td class="text-end">%s</td></tr>',
                htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                $this->price->format($product->price),
                $line['qty'],
                $this->price->format($line['line_total']),
            );
        }

        $user = $this->auth->currentUser();
        $name = htmlspecialchars($user?->displayName() ?? '', ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($user?->phone ?? '', ENT_QUOTES, 'UTF-8');

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>%s</h1>'
            . '%s'
            . '<div class="row"><div class="col-12 col-md-7">'
            . '<form method="post" action="/checkout/">'
            . '<div class="mb-3"><label>%s</label><input class="form-control" name="name" value="%s" required /></div>'
            . '<div class="mb-3"><label>%s</label><input class="form-control" name="phone" value="%s" required /></div>'
            . '<div class="mb-3"><label>%s</label><textarea class="form-control" name="address" required></textarea></div>'
            . '<div class="mb-3"><label>%s</label><textarea class="form-control" name="note"></textarea></div>'
            . '<button class="le-button huge" type="submit">%s</button>'
            . '</form>'
            . '</div><div class="col-12 col-md-5">'
            . '<h3>%s</h3>'
            . '<div class="table-responsive">'
            . '<table class="table">%s<tfoot><tr><th colspan="2" class="text-end">%s</th><th class="text-end">%s</th></tr></tfoot></table>'
            . '</div>'
            . '</div></div>'
            . '</section>',
            Lang::t('checkout.title'),
            $errorBox,
            Lang::t('checkout.name'),
            $name,
            Lang::t('checkout.phone'),
            $phone,
            Lang::t('checkout.address'),
            Lang::t('checkout.comment'),
            Lang::t('checkout.submit'),
            Lang::t('checkout.your_order'),
            $rows,
            Lang::t('checkout.total'),
            $this->price->format($this->cart->totalAmount()),
        );

        return Response::html($this->layout->render($body, new PageMeta(Lang::t('checkout.title')), $this->layout->breadcrumb(null, Lang::t('checkout.title'))));
    }
}
