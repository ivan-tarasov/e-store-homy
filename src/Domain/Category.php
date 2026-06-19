<?php

declare(strict_types=1);

namespace App\Domain;

use App\Support\Lang;

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
            name: (string) self::localized($row, 'name'),
            singular: (string) self::localized($row, 'singular'),
            icon: $row['icon'] ?? null,
            description: self::localized($row, 'description'),
        );
    }

    /**
     * Pick the English variant (`<field>_en`) when the UI locale is English and
     * a translation exists; otherwise fall back to the base (Russian) field.
     *
     * @param array<string, mixed> $row
     */
    private static function localized(array $row, string $field): mixed
    {
        if (Lang::isEnglish() && !empty($row[$field . '_en'])) {
            return $row[$field . '_en'];
        }
        return $row[$field] ?? null;
    }
}
