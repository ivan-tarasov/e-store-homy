<?php

declare(strict_types=1);

namespace App\Service;

final class PriceFormatter
{
    public function __construct(private readonly string $currencySymbol = ' ₽')
    {
    }

    public function format(int $amount): string
    {
        return number_format($amount, 0, ',', ' ') . $this->currencySymbol;
    }
}
