<?php

declare(strict_types=1);

namespace App\Domain;

final class Category
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $parentId,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $singular,
        public readonly ?string $icon = null,
        public readonly ?string $description = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            parentId: isset($row['parent_id']) ? (int) $row['parent_id'] : null,
            slug: (string) $row['slug'],
            name: (string) $row['name'],
            singular: (string) $row['singular'],
            icon: $row['icon'] ?? null,
            description: $row['description'] ?? null,
        );
    }
}
