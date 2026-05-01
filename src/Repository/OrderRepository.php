<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Order;
use App\Storage\JsonStore;

final class OrderRepository
{
    public function __construct(private readonly JsonStore $store)
    {
    }

    public function add(Order $order): void
    {
        $rows = $this->store->readOrEmpty('orders');
        $rows[] = $order->toArray();
        $this->store->write('orders', $rows);
    }

    /** @return list<Order> */
    public function forUser(int $userId): array
    {
        $rows = $this->store->readOrEmpty('orders');
        $orders = [];
        foreach ($rows as $row) {
            $order = Order::fromArray($row);
            if ($order->userId === $userId) {
                $orders[] = $order;
            }
        }
        usort($orders, static fn (Order $a, Order $b) => $b->createdAt <=> $a->createdAt);
        return $orders;
    }

    public function find(string $id): ?Order
    {
        $rows = $this->store->readOrEmpty('orders');
        foreach ($rows as $row) {
            if (($row['id'] ?? null) === $id) {
                return Order::fromArray($row);
            }
        }
        return null;
    }
}
