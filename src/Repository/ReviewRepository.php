<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Review;
use App\Storage\JsonStore;

final class ReviewRepository
{
    /** @var list<Review>|null */
    private ?array $cache = null;

    public function __construct(private readonly JsonStore $store)
    {
    }

    /** @return list<Review> */
    public function forProduct(int $productId): array
    {
        return array_values(array_filter(
            $this->loadAll(),
            static fn (Review $r) => $r->productId === $productId,
        ));
    }

    /** @return list<Review> */
    private function loadAll(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $rows = $this->store->readOrEmpty('reviews');
        $this->cache = array_map(Review::fromArray(...), $rows);
        return $this->cache;
    }
}
