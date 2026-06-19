<?php

declare(strict_types=1);

namespace App\Domain;

use App\Support\Lang;

final class Product
{
    /**
     * @param list<string>             $photos
     * @param array<string, string>    $properties
     */
    public function __construct(
        public readonly int $id,
        public readonly int $categoryId,
        public readonly int $brandId,
        public readonly string $name,
        public readonly string $description,
        public readonly int $price,
        public readonly int $stock,
        public readonly bool $inStock,
        public readonly float $rating,
        public readonly array $photos,
        public readonly array $properties,
        public readonly ?string $promoSlug = null,
    ) {
    }

    public function mainPhoto(): ?string
    {
        return $this->photos[0] ?? null;
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            categoryId: (int) $row['category_id'],
            brandId: (int) $row['brand_id'],
            name: (string) $row['name'],
            description: (string) (self::localized($row, 'description') ?? ''),
            price: (int) $row['price'],
            stock: (int) ($row['stock'] ?? 0),
            inStock: (bool) ($row['in_stock'] ?? false),
            rating: (float) ($row['rating'] ?? 0),
            photos: array_values(array_map(strval(...), $row['photos'] ?? [])),
            properties: self::localized($row, 'properties') ?? [],
            promoSlug: $row['promo_slug'] ?? null,
        );
    }

    /**
     * English variant (`<field>_en`) when the UI locale is English and a
     * translation exists; otherwise the base (Russian) field.
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
