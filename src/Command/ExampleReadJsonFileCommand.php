<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\ProductEncodableDto;
use App\Entity\Product;
use Symfony\Component\Encoder\DecoderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\TypeInfo\Type;

/**
 * This example how to read data from a JSON file.
 *
 * It demonstrate:
 * - How to deal with models that are composed by getters and setters
 * - How to customize the deserialization input (id is serialized as @id, price is formatted thanks to PriceFormatter)
 * - How it is possible to deserialize a big JSON input with a flat memory
 * - How it is possible to read JSON as lazily as possible
 *
 * The "new encoder" use case is a bit more complicated indeed, but is way more efficient.
 * It is addressed to developers with a bit more experience, seeking for performance.
 */
#[AsCommand(name: 'example:read-json-file')]
final class ExampleReadJsonFileCommand extends Command
{
    private string $productFilename;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly DecoderInterface $jsonDecoder,
        string $projectDir,
    ) {
        parent::__construct();

        $this->productFilename = sprintf('%s/products.json', $projectDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createProductsFromJsonUsingSerializer();
        $this->createProductsFromJsonUsingDecoder();
        $this->getTenthProductNameUsingEncoder();

        return Command::SUCCESS;
    }

    private function createProductsFromJsonUsingSerializer(): void
    {
        // the whole JSON is in memory
        $contents = file_get_contents($this->productFilename);

        // SerializedName attribute and ProductDenormalizer are involved during the denormalization
        // all products are instaniated and in memory as well
        $entities = $this->serializer->deserialize($contents, Product::class.'[]', 'json');

        dump($entities);
    }

    private function createProductsFromJsonUsingDecoder(): void
    {
        $contents = file_get_contents($this->productFilename);

        // EncodedName and DecodeFormatter are involved during the decoding
        // because the JsonEncoder only deal with public properties, we need to decode DTOs and then convert
        // them back to entities
        $dtos = $this->jsonDecoder->decode($contents, Type::list(Type::object(ProductEncodableDto::class)));
        $entities = array_map(ProductEncodableDto::toEntity(...), $dtos);

        dump($entities);
    }

    private function getTenthProductNameUsingEncoder(): void
    {
        // the JSON is read chunk by chunk, allowing memory to be flat
        // plus, as the type is iterableList, a generator is returned
        // it is as well possible to give a custom stream object
        $dtos = $this->jsonDecoder->decode(self::readFile($this->productFilename), Type::iterableList(Type::object(ProductEncodableDto::class)));

        foreach ($dtos as $i => $dto) {
            // right here, DTOs are lazy ghots
            // data is not read yet, only the structure has been detected
            if ($i < 10) {
                continue;
            }

            // when fetching a property, the JSON will actually be read within the involved boundaries only
            dump($dto->name);
            break;
        }
    }

    private static function readFile(string $filename): iterable
    {
        $resource = fopen($filename, 'r');

        while (!feof($resource)) {
            yield fread($resource, 16);
        }
    }
}
