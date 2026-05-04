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

final class OrdersAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
        private readonly PriceFormatter $price,
        private readonly RussianLocale $locale,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if (!$this->auth->isAdmin()) {
            return Response::redirect('/login/');
        }

        $orders = $this->orders->all();
        $rows = '';
        foreach ($orders as $order) {
            $rows .= sprintf(
                '<tr>'
                . '<td><a href="/admin/orders/%s">%s</a></td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td class="text-end">%s</td>'
                . '<td>%d</td>'
                . '<td>%s</td>'
                . '</tr>',
                rawurlencode($order->id),
                htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
                $this->locale->formatDateTime($order->createdAt),
                htmlspecialchars($order->customerName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($order->customerPhone, ENT_QUOTES, 'UTF-8'),
                $this->price->format($order->total),
                count($order->items),
                htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8'),
            );
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="7" class="text-muted text-center">Заказов ещё нет. Оформите тестовый заказ из <a href="/">витрины</a>.</td></tr>';
        }

        $body = sprintf(
            '<section class="container admin-page" style="padding:2em 0;">'
            . '<div class="row"><div class="col-md-3">%s</div><div class="col-md-9">'
            . '<h1>Заказы (%d)</h1>'
            . '<table class="table"><thead><tr>'
            . '<th>Номер</th><th>Дата</th><th>Клиент</th><th>Телефон</th><th class="text-end">Сумма</th><th>Поз.</th><th>Статус</th>'
            . '</tr></thead><tbody>%s</tbody></table>'
            . '</div></div></section>',
            AdminNav::render('orders'),
            count($orders),
            $rows,
        );

        return Response::html($this->layout->render($body, new PageMeta('Заказы — админ-панель')));
    }
}
