<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Product;
use App\Repository\ProductRepository;
use App\Support\Session;

/**
 * Session-backed shopping cart.
 *
 * The cart is stored as `[product_id => qty]` under one session key, which
 * makes the structure trivially serialisable and easy to inspect.
 */
final class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(
        private readonly Session $session,
        private readonly ProductRepository $products,
    ) {
    }

    /** @return array<int, int> map of product id to quantity */
    public function items(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function totalItems(): int
    {
        return count($this->items());
    }

    public function totalQty(): int
    {
        return array_sum($this->items());
    }

    public function totalAmount(): int
    {
        $total = 0;
        foreach ($this->lineItems() as $line) {
            $total += $line['line_total'];
        }
        return $total;
    }

    /** @return list<array{product:Product, qty:int, line_total:int}> */
    public function lineItems(): array
    {
        $items = $this->items();
        if ($items === []) {
            return [];
        }

        $products = $this->products->findMany(array_keys($items));
        $lines = [];
        foreach ($products as $product) {
            $qty = $items[$product->id];
            $lines[] = [
                'product' => $product,
                'qty' => $qty,
                'line_total' => $qty * $product->price,
            ];
        }
        return $lines;
    }

    public function add(int $productId, int $qty = 1): void
    {
        $items = $this->items();
        $items[$productId] = ($items[$productId] ?? 0) + $qty;
        $this->save($items);
    }

    public function setQty(int $productId, int $qty): void
    {
        $items = $this->items();
        if ($qty <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = $qty;
        }
        $this->save($items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        $this->save($items);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }

    public function has(int $productId): bool
    {
        return isset($this->items()[$productId]);
    }

    /** @param array<int, int> $items */
    private function save(array $items): void
    {
        if ($items === []) {
            $this->session->remove(self::SESSION_KEY);
            return;
        }
        $this->session->set(self::SESSION_KEY, $items);
    }
}
