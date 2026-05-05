<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repository\ProductRepository;
use App\Service\AuthService;

final class ProductDeleteAction
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly ProductRepository $products,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if (!$this->auth->isAdmin()) {
            return Response::redirect('/login/');
        }

        $id = (int) ($vars['id'] ?? 0);
        if ($id > 0) {
            $this->products->delete($id);
        }

        return Response::redirect('/admin/products/');
    }
}
