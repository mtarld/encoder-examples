<?php

declare(strict_types=1);

namespace App\Stream;

use Symfony\Component\Encoder\Stream\StreamWriterInterface;

final class OutputStream implements StreamWriterInterface
{
    /**
     * @var resource
     */
    private mixed $resource;

    public function __construct()
    {
        $this->resource = fopen('php://output', 'w+');
    }

    public function __destruct()
    {
        fclose($this->resource);
    }

    public function write(string $string): void
    {
        fwrite($this->resource, $string);
    }

    public function getResource(): mixed
    {
        return $this->resource;
    }
}
