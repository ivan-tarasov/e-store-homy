<?php

declare(strict_types=1);

namespace App\Domain;

final class Brand
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $homepage = null,
        public readonly ?string $logo = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            slug: (string) $row['slug'],
            name: (string) $row['name'],
            homepage: isset($row['homepage']) ? (string) $row['homepage'] : null,
            logo: isset($row['logo']) ? (string) $row['logo'] : null,
        );
    }

    /** Path to the brand logo, falling back to the text-wordmark svg. */
    public function logoPath(): string
    {
        return $this->logo ?? sprintf('/img/brands/%s.svg', $this->slug);
    }
}
