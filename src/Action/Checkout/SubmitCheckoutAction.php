<?php

declare(strict_types=1);

namespace App\Action\Checkout;

use App\Domain\Order;
use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Service\AuthService;
use App\Service\CartService;
use App\Support\Session;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class SubmitCheckoutAction
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderRepository $orders,
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly LayoutRenderer $layout,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($this->cart->isEmpty()) {
            return Response::redirect('/cart/');
        }

        $name = trim($request->input('name', '') ?? '');
        $phone = trim($request->input('phone', '') ?? '');
        $address = trim($request->input('address', '') ?? '');
        $note = trim($request->input('note', '') ?? '');

        if ($name === '' || $phone === '' || $address === '') {
            $this->session->setFlash('checkout_error', 'Заполните все обязательные поля.');
            return Response::redirect('/checkout/');
        }

        $items = [];
        foreach ($this->cart->lineItems() as $line) {
            $items[] = [
                'product_id' => $line['product']->id,
                'name' => $line['product']->name,
                'price' => $line['product']->price,
                'qty' => $line['qty'],
            ];
        }

        $order = new Order(
            id: 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4)),
            userId: $this->auth->currentUser()?->id,
            customerName: $name,
            customerPhone: $phone,
            address: $address,
            note: $note !== '' ? $note : null,
            items: $items,
            total: $this->cart->totalAmount(),
            createdAt: time(),
        );

        $this->orders->add($order);
        $this->cart->clear();

        $body = sprintf(
            '<section class="container" style="padding:3em 0;text-align:center;">'
            . '<h1>Спасибо, заказ оформлен!</h1>'
            . '<p>Номер вашего заказа: <strong>%s</strong></p>'
            . '<p class="text-muted">Это демо: заказ записан в <code>storage/runtime/orders.json</code>.</p>'
            . '<p><a class="le-button" href="/">На главную</a></p>'
            . '</section>',
            htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
        );

        return Response::html($this->layout->render($body, new PageMeta('Заказ оформлен')));
    }
}
