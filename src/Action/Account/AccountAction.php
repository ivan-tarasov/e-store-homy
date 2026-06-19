<?php

declare(strict_types=1);

namespace App\Action\Account;

use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Service\AuthService;
use App\Support\Lang;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class AccountAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            return Response::redirect('/login/');
        }

        $orderCount = count($this->orders->forUser($user->id));

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>%s</h1>'
            . '<p>%s</p>'
            . '<ul>'
            . '<li><a href="/my/orders/">%s</a></li>'
            . '<li><a href="/logout/">%s</a></li>'
            . '</ul>'
            . '</section>',
            Lang::t('account.title'),
            Lang::t('account.greeting', ['name' => '<strong>' . htmlspecialchars($user->displayName(), ENT_QUOTES, 'UTF-8') . '</strong>']),
            Lang::t('account.my_orders', ['n' => $orderCount]),
            Lang::t('account.logout'),
        );

        return Response::html($this->layout->render($body, new PageMeta(Lang::t('account.title'))));
    }
}
