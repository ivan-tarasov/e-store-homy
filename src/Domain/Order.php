<?php

declare(strict_types=1);

namespace App\Domain;

final class Order
{
    /** @param list<array{product_id:int, name:string, price:int, qty:int}> $items */
    public function __construct(
        public readonly string $id,
        public readonly ?int $userId,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly string $address,
        public readonly ?string $note,
        public readonly array $items,
        public readonly int $total,
        public readonly int $createdAt,
        public readonly string $status = 'new',
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'address' => $this->address,
            'note' => $this->note,
            'items' => $this->items,
            'total' => $this->total,
            'created_at' => $this->createdAt,
            'status' => $this->status,
        ];
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            userId: isset($row['user_id']) ? (int) $row['user_id'] : null,
            customerName: (string) $row['customer_name'],
            customerPhone: (string) $row['customer_phone'],
            address: (string) ($row['address'] ?? ''),
            note: $row['note'] ?? null,
            items: array_values($row['items'] ?? []),
            total: (int) $row['total'],
            createdAt: (int) $row['created_at'],
            status: (string) ($row['status'] ?? 'new'),
        );
    }
}
