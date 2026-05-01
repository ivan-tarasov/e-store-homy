<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;
use App\Support\Session;

final class SubmitLoginAction
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $email = trim($request->input('email', '') ?? '');
        $password = $request->input('password', '') ?? '';

        if ($email === '' || $password === '') {
            $this->session->setFlash('login_error', 'Введите e-mail и пароль.');
            return Response::redirect('/login/');
        }

        if ($this->auth->attempt($email, $password) === null) {
            $this->session->setFlash('login_error', 'Пара e-mail/пароль не подходит.');
            return Response::redirect('/login/');
        }

        return Response::redirect('/my/');
    }
}
