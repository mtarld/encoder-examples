<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Product;
use App\Service\PriceFormatter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ProductNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly PriceFormatter $priceFormatter,
    ) {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context['called'] = true;

        $normalized = $this->normalizer->normalize($object, $format, $context);
        $normalized['price'] = $this->priceFormatter->formatPriceToString($normalized['price']);

        // a new attribute dynamically added dependending on the data itself
        // this is unfortunately not possible with the JsonEncoder by default.
        // but there are workarounds:
        // - define cheap property all the time, with false value when needed
        // - intantiate specific object depending on the data
        // - normalize data using this normalizer, then encode the array thanks to the encoder
        if ($object->getPrice() < 2000) {
            $normalized['cheap'] = true;
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return !($context['called'] ?? false) && is_object($data) && Product::class === $data::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        // as soon as the normalizer calls itself a normalizer, this cannot be cached
        return ['*' => false];
    }
}
