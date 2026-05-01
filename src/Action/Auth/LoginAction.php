<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;
use App\Support\Session;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class LoginAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly AuthService $auth,
        private readonly Session $session,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($this->auth->isLoggedIn()) {
            return Response::redirect('/my/');
        }

        $error = $this->session->flash('login_error');
        $errorBox = $error !== null
            ? sprintf('<div class="alert alert-danger">%s</div>', htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8'))
            : '';

        $body = <<<HTML
<section class="container" style="padding:2em 0; max-width:480px;">
    <h1>Вход</h1>
    {$errorBox}
    <form method="post" action="/login/">
        <div class="form-group"><label>E-mail</label><input class="form-control" type="email" name="email" required autofocus /></div>
        <div class="form-group"><label>Пароль</label><input class="form-control" type="password" name="password" required /></div>
        <button class="le-button huge" type="submit">Войти</button>
    </form>
    <hr/>
    <p class="text-muted">
        В демо-версии есть тестовые пользователи:<br/>
        <code>demo@homy.local</code> / <code>demo</code><br/>
        <code>admin@homy.local</code> / <code>admin</code>
    </p>
</section>
HTML;

        return Response::html($this->layout->render($body, new PageMeta('Вход')));
    }
}
