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
use App\Template\TemplateEngine;

final class DashboardAction extends AbstractAdminAction
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
            $cardHtml .= $this->tpl->render('admin', 'dashboard-card', [
                'href'  => htmlspecialchars($href, ENT_QUOTES, 'UTF-8'),
                'value' => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
                'label' => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            ]);
        }

        $recent = array_slice($allOrders, 0, 5);
        $rows = '';
        foreach ($recent as $order) {
            $rows .= $this->tpl->render('admin', 'orders-row', [
                'id_encoded' => rawurlencode($order->id),
                'id'         => htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
                'date'       => $this->locale->formatDateTime($order->createdAt),
                'customer'   => htmlspecialchars($order->customerName, ENT_QUOTES, 'UTF-8'),
                'total'      => $this->price->format($order->total),
                'item_count' => count($order->items),
            ]);
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="5" class="text-muted text-center">Заказов ещё нет.</td></tr>';
        }

        $body = $this->tpl->render('admin', 'dashboard', [
            'nav'   => AdminNav::render('dashboard'),
            'cards' => $cardHtml,
            'rows'  => $rows,
        ]);

        return Response::html($this->layout->render($body, new PageMeta('Сводка — админ-панель')));
    }
}
