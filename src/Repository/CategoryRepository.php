<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Category;
use App\Storage\JsonStore;

final class CategoryRepository
{
    /** @var array<int, Category>|null */
    private ?array $byId = null;

    public function __construct(private readonly JsonStore $store)
    {
    }

    /** @return list<Category> */
    public function all(): array
    {
        return array_values($this->loadById());
    }

    /** @return list<Category> */
    public function rootCategories(): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Category $c) => $c->parentId === null,
        ));
    }

    /** @return list<Category> */
    public function children(int $parentId): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Category $c) => $c->parentId === $parentId,
        ));
    }

    public function find(int $id): ?Category
    {
        return $this->loadById()[$id] ?? null;
    }

    public function findBySlug(string $slug): ?Category
    {
        foreach ($this->loadById() as $cat) {
            if ($cat->slug === $slug) {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Path of categories from the root down to $catId, used for breadcrumbs.
     *
     * @return list<Category>
     */
    public function path(int $catId): array
    {
        $byId = $this->loadById();
        if (!isset($byId[$catId])) {
            return [];
        }

        $stack = [];
        $cursor = $byId[$catId];
        while ($cursor !== null) {
            $stack[] = $cursor;
            $cursor = $cursor->parentId !== null ? ($byId[$cursor->parentId] ?? null) : null;
        }

        return array_reverse($stack);
    }

    /** @return array<int, Category> */
    private function loadById(): array
    {
        if ($this->byId !== null) {
            return $this->byId;
        }

        $byId = [];
        foreach ($this->store->read('categories') as $row) {
            $cat = Category::fromArray($row);
            $byId[$cat->id] = $cat;
        }
        return $this->byId = $byId;
    }
}
