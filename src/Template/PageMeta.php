<?php

declare(strict_types=1);

namespace App\Template;

final class PageMeta
{
    public function __construct(
        public readonly string $title,
        public readonly string $description = '',
        public readonly string $keywords = '',
    ) {
    }
}
