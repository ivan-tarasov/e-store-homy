<?php

declare(strict_types=1);

namespace App\Action\Account;

use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Service\AuthService;
use App\Service\RussianLocale;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class OrdersAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
        private readonly RussianLocale $locale,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            return Response::redirect('/login/');
        }

        $orders = $this->orders->forUser($user->id);
        $rows = '';
        foreach ($orders as $order) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%d %s</td><td class="text-end">%s</td><td>%s</td></tr>',
                htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
                $this->locale->formatDateTime($order->createdAt),
                count($order->items),
                $this->locale->plural(count($order->items), ['позиция', 'позиции', 'позиций']),
                number_format($order->total, 0, ',', ' ') . ' ₽',
                htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8'),
            );
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5" class="text-muted text-center">У вас ещё нет заказов.</td></tr>';
        }

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>Мои заказы</h1>'
            . '<table class="table"><thead><tr>'
            . '<th>Номер</th><th>Дата</th><th>Позиций</th><th class="text-end">Сумма</th><th>Статус</th>'
            . '</tr></thead><tbody>%s</tbody></table>'
            . '<p><a href="/my/">← В кабинет</a></p>'
            . '</section>',
            $rows,
        );

        return Response::html($this->layout->render($body, new PageMeta('Мои заказы')));
    }
}
