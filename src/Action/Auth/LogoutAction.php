<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Http\Request;
use App\Http\Response;
use App\Service\AuthService;

final class LogoutAction
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $this->auth->logout();
        return Response::redirect('/');
    }
}
