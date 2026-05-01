<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Product;
use App\Storage\JsonStore;

final class ProductRepository
{
    /** @var array<int, Product>|null */
    private ?array $byId = null;

    public function __construct(
        private readonly JsonStore $store,
        private readonly CategoryRepository $categories,
    ) {
    }

    public function find(int $id): ?Product
    {
        return $this->loadById()[$id] ?? null;
    }

    /**
     * @param list<int> $ids
     * @return list<Product>
     */
    public function findMany(array $ids): array
    {
        $all = $this->loadById();
        $out = [];
        foreach ($ids as $id) {
            if (isset($all[$id])) {
                $out[] = $all[$id];
            }
        }
        return $out;
    }

    /** @return list<Product> */
    public function all(): array
    {
        return array_values($this->loadById());
    }

    /** @return list<Product> */
    public function newestWithImages(int $limit): array
    {
        $items = array_filter(
            $this->all(),
            static fn (Product $p) => $p->mainPhoto() !== null,
        );

        // newest = highest id
        usort($items, static fn (Product $a, Product $b) => $b->id <=> $a->id);

        return array_slice(array_values($items), 0, $limit);
    }

    /**
     * Products in a given category subtree, optionally filtered by brand,
     * sorted by one of: 'price-asc', 'price-desc', 'rating-desc', or default
     * (in-stock first, then id desc).
     *
     * @return list<Product>
     */
    public function inCategory(int $categoryId, ?int $brandId, string $sort, int $offset, int $limit): array
    {
        [$items, $_] = $this->collectInCategoryFiltered($categoryId, $brandId);
        $this->sort($items, $sort);

        return array_slice($items, $offset, $limit);
    }

    /**
     * Total count of products in a category subtree, optionally filtered by brand.
     */
    public function countInCategory(int $categoryId, ?int $brandId): int
    {
        [$items, $_] = $this->collectInCategoryFiltered($categoryId, $brandId);
        return count($items);
    }

    /**
     * Brands present in a category subtree, with counts.
     *
     * @return list<array{brand_id: int, count: int}>
     */
    public function brandFacets(int $categoryId): array
    {
        [$_, $brandCounts] = $this->collectInCategoryFiltered($categoryId, null);

        $facets = [];
        foreach ($brandCounts as $brandId => $count) {
            $facets[] = ['brand_id' => $brandId, 'count' => $count];
        }
        usort($facets, static fn ($a, $b) => $b['count'] <=> $a['count']);
        return $facets;
    }

    /**
     * 5 highest-rated products per immediate sub-category. Used on the
     * category landing page to show carousels.
     *
     * @return list<Product>
     */
    public function topRatedInCategory(int $categoryId, int $limit): array
    {
        [$items, $_] = $this->collectInCategoryFiltered($categoryId, null);
        usort($items, static fn (Product $a, Product $b) => $b->rating <=> $a->rating);

        return array_slice($items, 0, $limit);
    }

    /**
     * Same-category products, excluding $excludeId, sorted randomly.
     *
     * @return list<Product>
     */
    public function inSameCategory(int $categoryId, int $excludeId, int $limit): array
    {
        [$items, $_] = $this->collectInCategoryFiltered($categoryId, null);
        $items = array_values(array_filter(
            $items,
            static fn (Product $p) => $p->id !== $excludeId && $p->inStock && $p->mainPhoto() !== null,
        ));
        shuffle($items);
        return array_slice($items, 0, $limit);
    }

    /** @return list<Product> */
    public function search(string $term, int $limit): array
    {
        $term = trim(mb_strtolower($term));
        if ($term === '') {
            return [];
        }

        $words = preg_split('/\s+/u', $term) ?: [$term];
        $scored = [];
        foreach ($this->all() as $product) {
            $brand = '';
            $category = '';
            $haystack = mb_strtolower(implode(' ', [
                $product->name,
                $product->description,
            ]));
            $score = 0;
            foreach ($words as $word) {
                if ($word === '') {
                    continue;
                }
                if (str_contains($haystack, $word)) {
                    $score++;
                }
            }
            if ($score > 0 || (string) $product->id === $term) {
                $scored[] = [$score, $product];
            }
        }

        usort($scored, static fn ($a, $b) => $b[0] <=> $a[0] ?: $b[1]->rating <=> $a[1]->rating);
        return array_slice(array_map(static fn ($pair) => $pair[1], $scored), 0, $limit);
    }

    /** @return array{0: list<Product>, 1: array<int, int>} */
    private function collectInCategoryFiltered(int $categoryId, ?int $brandId): array
    {
        $catIds = $this->subtreeIds($categoryId);
        $brandCounts = [];
        $items = [];

        foreach ($this->all() as $product) {
            if (!in_array($product->categoryId, $catIds, true)) {
                continue;
            }
            $brandCounts[$product->brandId] = ($brandCounts[$product->brandId] ?? 0) + 1;
            if ($brandId !== null && $product->brandId !== $brandId) {
                continue;
            }
            $items[] = $product;
        }

        return [$items, $brandCounts];
    }

    /**
     * @param list<Product> $items
     */
    private function sort(array &$items, string $sort): void
    {
        usort($items, match ($sort) {
            'price-asc' => static fn (Product $a, Product $b) => $a->price <=> $b->price,
            'price-desc' => static fn (Product $a, Product $b) => $b->price <=> $a->price,
            'rating-desc' => static fn (Product $a, Product $b) => $b->rating <=> $a->rating,
            default => static function (Product $a, Product $b) {
                if ($a->inStock !== $b->inStock) {
                    return $b->inStock <=> $a->inStock;
                }
                return $b->id <=> $a->id;
            },
        });
    }

    /** @return list<int> */
    private function subtreeIds(int $rootId): array
    {
        $ids = [$rootId];
        $queue = [$rootId];
        while ($queue !== []) {
            $current = array_pop($queue);
            foreach ($this->categories->children($current) as $child) {
                $ids[] = $child->id;
                $queue[] = $child->id;
            }
        }
        return $ids;
    }

    /** @return array<int, Product> */
    private function loadById(): array
    {
        if ($this->byId !== null) {
            return $this->byId;
        }

        $byId = [];
        foreach ($this->store->read('products') as $row) {
            $product = Product::fromArray($row);
            $byId[$product->id] = $product;
        }
        return $this->byId = $byId;
    }
}
