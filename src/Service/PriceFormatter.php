<?php

declare(strict_types=1);

namespace App\Service;

final class PriceFormatter
{
    public function formatPriceToString(int $price): string
    {
        return sprintf('%.2f eur', $price / 100);
    }

    public function formatStringToPrice(string $string): int
    {
        return (int) (100 * ((float) str_replace(' eur', '', $string)));
    }
}
