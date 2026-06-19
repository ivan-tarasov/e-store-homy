<?php

declare(strict_types=1);

namespace App\Action\Account;

use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Service\AuthService;
use App\Service\RussianLocale;
use App\Support\Lang;
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
            $itemCount = count($order->items);
            $itemWord = Lang::isEnglish()
                ? Lang::t($itemCount === 1 ? 'word.items.one' : 'word.items.few')
                : $this->locale->plural($itemCount, [
                    Lang::t('word.items.one'),
                    Lang::t('word.items.few'),
                    Lang::t('word.items.many'),
                ]);
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%d %s</td><td class="text-end">%s</td><td>%s</td></tr>',
                htmlspecialchars($order->id, ENT_QUOTES, 'UTF-8'),
                $this->locale->formatDateTime($order->createdAt),
                $itemCount,
                $itemWord,
                number_format($order->total, 0, ',', ' ') . ' ₽',
                htmlspecialchars($order->status, ENT_QUOTES, 'UTF-8'),
            );
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5" class="text-muted text-center">' . Lang::t('orders.none') . '</td></tr>';
        }

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>%s</h1>'
            . '<table class="table"><thead><tr>'
            . '<th>%s</th><th>%s</th><th>%s</th><th class="text-end">%s</th><th>%s</th>'
            . '</tr></thead><tbody>%s</tbody></table>'
            . '<p><a href="/my/">%s</a></p>'
            . '</section>',
            Lang::t('orders.title'),
            Lang::t('orders.col_number'),
            Lang::t('orders.col_date'),
            Lang::t('orders.col_items'),
            Lang::t('orders.col_sum'),
            Lang::t('orders.col_status'),
            $rows,
            Lang::t('orders.back'),
        );

        return Response::html($this->layout->render($body, new PageMeta(Lang::t('orders.title'))));
    }
}
