<?php

namespace App\Dto;

use Symfony\Component\JsonEncoder\Attribute\DecodeFormatter;
use Symfony\Component\JsonEncoder\Attribute\EncodedName;
use Symfony\Component\JsonEncoder\Attribute\EncodeFormatter;
use Symfony\Component\JsonEncoder\Attribute\MaxDepth;

class Dummy
{
    #[EncodedName('@id')]
    public int $id;

    #[EncodeFormatter('strtoupper')]
    public string $name;

    public int $price;
}