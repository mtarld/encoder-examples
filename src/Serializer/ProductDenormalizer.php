<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Product;
use App\Service\PriceFormatter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ProductDenormalizer implements DenormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly PriceFormatter $priceFormatter,
    ) {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $context['called'] = true;
        $data['price'] = $this->priceFormatter->formatStringToPrice($data['price']);

        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return !($context['called'] ?? false) && Product::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        // as soon as the normalizer calls itself a normalizer, this cannot be cached
        return ['*' => false];
    }
}
