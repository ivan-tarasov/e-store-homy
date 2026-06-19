<?php

declare(strict_types=1);

namespace App\Action\Cart;

use App\Http\Request;
use App\Http\Response;
use App\Service\CartService;
use App\Service\PriceFormatter;

final class UpdateCartAction
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PriceFormatter $price,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $id = $request->int('id');
        $qty = max(0, $request->int('qty', 0));

        if ($id > 0) {
            $this->cart->setQty($id, $qty);
        }

        if ($request->isAjax()) {
            return Response::json([
                'ok' => true,
                'total' => $this->price->format($this->cart->totalAmount()),
                'total_qty' => $this->cart->totalQty(),
            ]);
        }
        return Response::redirect('/cart/');
    }
}
