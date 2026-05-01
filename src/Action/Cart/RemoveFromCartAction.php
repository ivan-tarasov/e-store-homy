<?php

declare(strict_types=1);

namespace App\Action\Cart;

use App\Http\Request;
use App\Http\Response;
use App\Service\CartService;

final class RemoveFromCartAction
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $id = $request->int('id');
        if ($id > 0) {
            $this->cart->remove($id);
        }
        return Response::redirect($request->server['HTTP_REFERER'] ?? '/cart/');
    }
}
