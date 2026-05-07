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

final class UsersAction extends AbstractAdminAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly UserRepository $users,
        private readonly OrderRepository $orders,
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
            $rows .= sprintf(
                '<tr>'
                . '<td>%d</td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td class="text-end">%d</td>'
                . '</tr>',
                $user->id,
                htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($user->displayName(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($user->phone ?? '—', ENT_QUOTES, 'UTF-8'),
                $user->isAdmin ? '<span class="stock-pill stock-in">админ</span>' : '<span class="text-muted">—</span>',
                $orderCount,
            );
        }

        $body = sprintf(
            '<section class="container admin-page" style="padding:2em 0;">'
            . '<div class="row"><div class="col-12 col-md-3">%s</div><div class="col-12 col-md-9">'
            . '<h1>Пользователи (%d)</h1>'
            . '<p class="text-muted">Демо-пароли в документации: <code>demo@homy.local</code> / <code>demo</code>, <code>admin@homy.local</code> / <code>admin</code>.</p>'
            . '<table class="table"><thead><tr>'
            . '<th>ID</th><th>E-mail</th><th>Имя</th><th>Телефон</th><th>Роль</th><th class="text-end">Заказов</th>'
            . '</tr></thead><tbody>%s</tbody></table>'
            . '</div></div></section>',
            AdminNav::render('users'),
            count($users),
            $rows,
        );

        return Response::html($this->layout->render($body, new PageMeta('Пользователи — админ-панель')));
    }
}
