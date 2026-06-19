<?php

declare(strict_types=1);

namespace App\Domain;

final class Review
{
    public function __construct(
        public readonly int $productId,
        public readonly string $author,
        public readonly int $grade,
        public readonly string $comment,
        public readonly ?string $pros,
        public readonly ?string $cons,
        public readonly int $createdAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            productId: (int) $row['product_id'],
            author: (string) ($row['author'] ?? 'Anonymous'),
            grade: (int) ($row['grade'] ?? 5),
            comment: (string) ($row['comment'] ?? ''),
            pros: $row['pros'] ?? null,
            cons: $row['cons'] ?? null,
            createdAt: (int) ($row['created_at'] ?? time()),
        );
    }
}
