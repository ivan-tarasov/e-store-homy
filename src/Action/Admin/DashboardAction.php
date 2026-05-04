<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Domain\Order;
use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\PriceFormatter;
use App\Service\RussianLocale;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class DashboardAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
        private readonly UserRepository $users,
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

        $allOrders = $this->orders->all();
        $totalRevenue = array_sum(array_map(static fn (Order $o) => $o->total, $allOrders));
        $inStock = count(array_filter($this->products->all(), static fn ($p) => $p->inStock));

        $cards = [
            ['Заказов', count($allOrders), '/admin/orders/'],
            ['Выручка', $this->price->format($totalRevenue), '/admin/orders/'],
            ['Товаров', count($this->products->all()), '/admin/products/'],
            ['В наличии', $inStock, '/admin/products/'],
            ['Категорий', count($this->categories->all()), '#'],
            ['Брендов', count($this->brands->all()), '#'],
            ['Пользователей', count($this->users->all()), '/admin/users/'],
        ];

        $cardHtml = '';
        foreach ($cards as [$label, $value, $href]) {
            $cardHtml .= sprintf(
                '<a href="%s" class="admin-card"><div class="admin-card-value">%s</div><div class="admin-card-label">%s</div></a>',
                htmlspecialchars($href, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            );
        }

        $recent = array_slice($allOrders, 0, 5);
        $rows = '';
        foreach ($recent as $order) {
            $rows .= sprintf(
                '<tr><td><a href="/admin/orders/%s">%s</a></td><td>%s</td><td>%s</td><td class="text-end">%s</td><td>%d поз.</td></tr>',
                rawurlencode($order->id),
                htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
                $this->locale->formatDateTime($order->createdAt),
                htmlspecialchars($order->customerName, ENT_QUOTES, 'UTF-8'),
                $this->price->format($order->total),
                count($order->items),
            );
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="5" class="text-muted text-center">Заказов ещё нет.</td></tr>';
        }

        $body = sprintf(
            '<section class="container admin-page" style="padding:2em 0;">'
            . '<div class="row"><div class="col-md-3">%s</div><div class="col-md-9">'
            . '<h1>Панель администратора</h1>'
            . '<div class="admin-cards">%s</div>'
            . '<h2 style="margin-top:2em;">Последние заказы</h2>'
            . '<table class="table"><thead><tr>'
            . '<th>Номер</th><th>Дата</th><th>Клиент</th><th class="text-end">Сумма</th><th>Позиций</th>'
            . '</tr></thead><tbody>%s</tbody></table>'
            . '<p><a href="/admin/orders/">Все заказы →</a></p>'
            . '</div></div></section>',
            AdminNav::render('dashboard'),
            $cardHtml,
            $rows,
        );

        return Response::html($this->layout->render($body, new PageMeta('Сводка — админ-панель')));
    }
}
