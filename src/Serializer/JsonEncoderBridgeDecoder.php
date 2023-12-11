<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Encoder\DecoderInterface as NewDecoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\TypeInfo\Type;

final class JsonEncoderBridgeDecoder implements DecoderInterface
{
    public function __construct(
        private readonly NewDecoderInterface $jsonDecoder,
    ) {
    }

    public function decode(string $data, string $format, array $context = []): mixed
    {
        return $this->jsonDecoder->decode($data, Type::array(), $context);
    }

    public function supportsDecoding(string $format): bool
    {
        return 'json_bridged' === $format;
    }
}
