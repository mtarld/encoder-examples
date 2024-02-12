<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\JsonEncoder\EncoderInterface as NewEncoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

final class JsonEncoderBridgeEncoder implements EncoderInterface
{
    public function __construct(
        private readonly NewEncoderInterface $jsonEncoder,
    ) {
    }

    public function encode(mixed $data, string $format, array $context = []): string
    {
        return (string) $this->jsonEncoder->encode($data, $context);
    }

    public function supportsEncoding(string $format): bool
    {
        return 'json_bridged' === $format;
    }
}
