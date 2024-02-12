<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Product;
use App\Service\PriceFormatter;
use Symfony\Component\JsonEncoder\Attribute\EncodedName;
use Symfony\Component\JsonEncoder\Attribute\EncodeFormatter;
use Symfony\Component\JsonEncoder\Attribute\DecodeFormatter;

class ProductEncodableDto
{
    #[EncodedName('@id')]
    public int $id;

    public string $name;

    #[EncodeFormatter([self::class, 'convertPriceToString'])]
    #[DecodeFormatter([self::class, 'convertStringToPrice'])]
    public int $price;

    public static function convertPriceToString(int $price, PriceFormatter $priceFormatter): string
    {
        return $priceFormatter->formatPriceToString($price);
    }

    public static function convertStringToPrice(string $string, PriceFormatter $priceFormatter): int
    {
        return $priceFormatter->formatStringToPrice($string);
    }

    public static function toEntity(self $dto): Product
    {
        return (new Product())
            ->setId($dto->id)
            ->setName($dto->name)
            ->setPrice($dto->price);
    }
}
