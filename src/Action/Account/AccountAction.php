<?php

declare(strict_types=1);

namespace App\Action\Account;

use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Service\AuthService;
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
            . '<h1>Личный кабинет</h1>'
            . '<p>Здравствуйте, <strong>%s</strong>!</p>'
            . '<ul>'
            . '<li><a href="/my/orders/">Мои заказы (%d)</a></li>'
            . '<li><a href="/logout/">Выйти</a></li>'
            . '</ul>'
            . '</section>',
            htmlspecialchars($user->displayName(), ENT_QUOTES, 'UTF-8'),
            $orderCount,
        );

        return Response::html($this->layout->render($body, new PageMeta('Личный кабинет')));
    }
}
