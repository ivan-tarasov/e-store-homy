<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Action\Errors\NotFoundAction;
use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\PriceFormatter;
use App\Service\RussianLocale;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class OrderShowAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
        private readonly UserRepository $users,
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

        $order = $this->orders->find($vars['id'] ?? '');
        if ($order === null) {
            return (new NotFoundAction($this->layout))($request, $vars);
        }

        $itemRows = '';
        foreach ($order->items as $item) {
            $lineTotal = (int) $item['price'] * (int) $item['qty'];
            $itemRows .= sprintf(
                '<tr><td>#%d</td><td>%s</td><td class="text-end">%s</td><td class="text-center">%d</td><td class="text-end">%s</td></tr>',
                (int) $item['product_id'],
                htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8'),
                $this->price->format((int) $item['price']),
                (int) $item['qty'],
                $this->price->format($lineTotal),
            );
        }

        $user = $order->userId !== null ? $this->users->find($order->userId) : null;
        $userLine = $user !== null
            ? sprintf(
                'Зарегистрированный: <a href="/admin/users/">%s</a>',
                htmlspecialchars($user->displayName(), ENT_QUOTES, 'UTF-8'),
            )
            : 'Гостевой заказ (без аккаунта)';

        $note = $order->note !== null
            ? '<p><strong>Комментарий:</strong> ' . htmlspecialchars($order->note, ENT_QUOTES, 'UTF-8') . '</p>'
            : '';

        $body = sprintf(
            '<section class="container admin-page" style="padding:2em 0;">'
            . '<div class="row"><div class="col-md-3">%s</div><div class="col-md-9">'
            . '<p><a href="/admin/orders/">← Все заказы</a></p>'
            . '<h1>Заказ %s</h1>'
            . '<div class="admin-meta-grid">'
            . '<div><label>Создан</label><div>%s</div></div>'
            . '<div><label>Статус</label><div>%s</div></div>'
            . '<div><label>Клиент</label><div>%s</div></div>'
            . '<div><label>Телефон</label><div>%s</div></div>'
            . '<div><label>Адрес</label><div>%s</div></div>'
            . '<div><label>Аккаунт</label><div>%s</div></div>'
            . '</div>'
            . '%s'
            . '<h2 style="margin-top:2em;">Позиции</h2>'
            . '<table class="table"><thead><tr>'
            . '<th>ID</th><th>Товар</th><th class="text-end">Цена</th><th class="text-center">Кол-во</th><th class="text-end">Сумма</th>'
            . '</tr></thead><tbody>%s</tbody>'
            . '<tfoot><tr><th colspan="4" class="text-end">Итого</th><th class="text-end">%s</th></tr></tfoot></table>'
            . '</div></div></section>',
            AdminNav::render('orders'),
            htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
            $this->locale->formatDateTime($order->createdAt),
            htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($order->customerName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($order->customerPhone, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'),
            $userLine,
            $note,
            $itemRows,
            $this->price->format($order->total),
        );

        return Response::html($this->layout->render(
            $body,
            new PageMeta('Заказ ' . $order->id . ' — админ-панель'),
        ));
    }
}
