<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class UsersAction extends AbstractAdminAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly UserRepository $users,
        private readonly OrderRepository $orders,
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

        $users = $this->users->all();
        $rows = '';
        foreach ($users as $user) {
            $orderCount = count($this->orders->forUser($user->id));
            $rows .= $this->tpl->render('admin', 'users-row', [
                'id'          => $user->id,
                'email'       => htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'),
                'name'        => htmlspecialchars($user->displayName(), ENT_QUOTES, 'UTF-8'),
                'phone'       => htmlspecialchars($user->phone ?? '—', ENT_QUOTES, 'UTF-8'),
                'role'        => $user->isAdmin ? '<span class="stock-pill stock-in">админ</span>' : '<span class="text-muted">—</span>',
                'order_count' => $orderCount,
            ]);
        }

        $body = $this->tpl->render('admin', 'users', [
            'nav'   => AdminNav::render('users'),
            'count' => count($users),
            'rows'  => $rows,
        ]);

        return Response::html($this->layout->render($body, new PageMeta('Пользователи — админ-панель')));
    }
}
