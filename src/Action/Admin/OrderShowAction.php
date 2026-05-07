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
use App\Template\TemplateEngine;

final class OrderShowAction extends AbstractAdminAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
        private readonly UserRepository $users,
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

        $order = $this->orders->find($vars['id'] ?? '');
        if ($order === null) {
            return (new NotFoundAction($this->layout))($request, $vars);
        }

        $itemRows = '';
        foreach ($order->items as $item) {
            $lineTotal = (int) $item['price'] * (int) $item['qty'];
            $itemRows .= $this->tpl->render('admin', 'order-item-row', [
                'product_id' => (int) $item['product_id'],
                'name'       => htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8'),
                'price'      => $this->price->format((int) $item['price']),
                'qty'        => (int) $item['qty'],
                'line_total' => $this->price->format($lineTotal),
            ]);
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

        $body = $this->tpl->render('admin', 'order-show', [
            'nav'       => AdminNav::render('orders'),
            'id'        => htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
            'created'   => $this->locale->formatDateTime($order->createdAt),
            'status'    => htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8'),
            'customer'  => htmlspecialchars($order->customerName, ENT_QUOTES, 'UTF-8'),
            'phone'     => htmlspecialchars($order->customerPhone, ENT_QUOTES, 'UTF-8'),
            'address'   => htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'),
            'user_line' => $userLine,
            'note'      => $note,
            'rows'      => $itemRows,
            'total'     => $this->price->format($order->total),
        ]);

        return Response::html($this->layout->render(
            $body,
            new PageMeta('Заказ ' . $order->id . ' — админ-панель'),
        ));
    }
}
