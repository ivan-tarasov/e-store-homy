<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Service\AuthService;
use App\Service\PriceFormatter;
use App\Service\RussianLocale;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class OrdersAction extends AbstractAdminAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
        private readonly PriceFormatter $price,
        private readonly RussianLocale $locale,
        private readonly TemplateEngine $tpl,
    ) {
    }

    protected function auth(): AuthService { return $this->auth; }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $orders = $this->orders->all();
        $rows = '';
        foreach ($orders as $order) {
            $rows .= $this->tpl->render('admin', 'orders-list-row', [
                'id_encoded' => rawurlencode($order->id),
                'id'         => htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
                'date'       => $this->locale->formatDateTime($order->createdAt),
                'customer'   => htmlspecialchars($order->customerName, ENT_QUOTES, 'UTF-8'),
                'phone'      => htmlspecialchars($order->customerPhone, ENT_QUOTES, 'UTF-8'),
                'total'      => $this->price->format($order->total),
                'item_count' => count($order->items),
                'status'     => htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8'),
            ]);
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="7" class="text-muted text-center">Заказов ещё нет. Оформите тестовый заказ из <a href="/">витрины</a>.</td></tr>';
        }

        $body = $this->tpl->render('admin', 'orders', [
            'nav'   => AdminNav::render('orders'),
            'count' => count($orders),
            'rows'  => $rows,
        ]);

        return Response::html($this->layout->render($body, new PageMeta('Заказы — админ-панель')));
    }
}
