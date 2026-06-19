<?php

declare(strict_types=1);

namespace App\Action\Cart;

use App\Http\Request;
use App\Http\Response;
use App\Service\CartService;

final class AddToCartAction
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $id = $request->int('id');
        $qty = max(1, $request->int('qty', 1));
        if ($id <= 0) {
            return Response::redirect($request->server['HTTP_REFERER'] ?? '/');
        }

        $this->cart->add($id, $qty);

        if ($request->isAjax()) {
            return Response::json([
                'ok' => true,
                'total_qty' => $this->cart->totalQty(),
            ]);
        }
        return Response::redirect($request->server['HTTP_REFERER'] ?? '/cart/');
    }
}
