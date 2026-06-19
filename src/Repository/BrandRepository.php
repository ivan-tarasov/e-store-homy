<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Brand;
use App\Storage\JsonStore;

final class BrandRepository
{
    /** @var array<int, Brand>|null */
    private ?array $byId = null;

    public function __construct(private readonly JsonStore $store)
    {
    }

    /** @return list<Brand> */
    public function all(): array
    {
        return array_values($this->loadById());
    }

    public function find(int $id): ?Brand
    {
        return $this->loadById()[$id] ?? null;
    }

    public function findBySlug(string $slug): ?Brand
    {
        foreach ($this->loadById() as $brand) {
            if ($brand->slug === $slug) {
                return $brand;
            }
        }
        return null;
    }

    /** @return array<int, Brand> */
    private function loadById(): array
    {
        if ($this->byId !== null) {
            return $this->byId;
        }

        $byId = [];
        foreach ($this->store->read('brands') as $row) {
            $brand = Brand::fromArray($row);
            $byId[$brand->id] = $brand;
        }
        return $this->byId = $byId;
    }
}
