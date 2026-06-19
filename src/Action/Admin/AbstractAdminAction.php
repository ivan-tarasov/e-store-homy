<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Response;
use App\Service\AuthService;

abstract class AbstractAdminAction
{
    abstract protected function auth(): AuthService;

    /**
     * Returns a redirect response if the current user is not an admin, null otherwise.
     * Usage: if ($redirect = $this->requireAdmin()) { return $redirect; }
     */
    protected function requireAdmin(): ?Response
    {
        if (!$this->auth()->isAdmin()) {
            return Response::redirect('/login/');
        }
        return null;
    }
}
