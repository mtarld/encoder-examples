<?php

namespace App\Command;

use App\Dto\ProductEncodableDto;
use App\Entity\Product;
use App\Stream\OutputStream;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\JsonEncoder\EncoderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\TypeInfo\Type;

/**
 * This example shows how to fetch entities from database using doctrine
 * to serialize them and display them in php://output (which can be the HTTP response)
 *
 * This example demonstrate:
 * - How to deal with models that are composed by getters and setters
 * - How to customize the serialization output (id is serialized as @id, price is formatted thanks to PriceFormatter)
 * - How it is possible to serialize a whole table contents to output with a flat memory
 *
 * The second use case is a bit more complicated indeed, but is way more efficient.
 * It is addressed to developers with a bit more experience, seeking for performance.
 */
#[AsCommand(name: 'example:from-database-to-output')]
class ExampleFromDatabaseToOutputCommand extends Command
{
    private readonly EntityRepository $productRepository;

    public function __construct(
        EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly EncoderInterface $jsonEncoder,
    ) {
        parent::__construct();

        $this->productRepository = $em->getRepository(Product::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->echoProductFromDatabaseAsJsonUsingSerializer();
        $this->echoProductFromDatabaseAsJsonUsingEncoder();
        $this->echoProductFromDatabaseAsJsonUsingEncoderAndStreamObject();

        return Command::SUCCESS;
    }

    private function echoProductFromDatabaseAsJsonUsingSerializer(): void
    {
        // all products are in memory
        $productsFromDatabase = $this->productRepository->findAll();

        // SerializedName attribute and ProductNormalizer are involved during the normalization
        // at the end, the whole JSON string is in memory
        echo $this->serializer->serialize($productsFromDatabase, 'json');
    }

    private function echoProductFromDatabaseAsJsonUsingEncoder(): void
    {
        // an iterator is retrieved
        $productsFromDatabase = $this->productRepository->createQueryBuilder('p')->getQuery()->toIterable();

        // EncodedName and EncodeFormatter are involved during the encoding
        // because the JsonEncoder only deal with public properties, we need to convert products to DTOs
        // this can be the role of a mapper.
        // we still do not have all the products in memory at once
        $convertedProductsFromDatabase = self::mapIterator($productsFromDatabase, Product::toEncodableDto(...));

        // the generated stream is a traversable list of chunk
        // therefore we do not have the whole JSON string in memory
        $encoded = $this->jsonEncoder->encode($convertedProductsFromDatabase, [
            'type' => Type::iterableList(Type::object(ProductEncodableDto::class)),
        ]);

        foreach ($encoded as $chunk) {
            echo $chunk;
        }
    }

    private function echoProductFromDatabaseAsJsonUsingEncoderAndStreamObject(): void
    {
        $productsFromDatabase = $this->productRepository->createQueryBuilder('p')->getQuery()->toIterable();
        $convertedProductsFromDatabase = self::mapIterator($productsFromDatabase, Product::toEncodableDto(...));

        // the generated stream is directly redirected to PHP output thanks to the stream object
        // therefore we still do not have the whole JSON string in memory
        $this->jsonEncoder->encode($convertedProductsFromDatabase, [
            'type' => Type::iterableList(Type::object(ProductEncodableDto::class)),
            'stream' => new OutputStream(),
        ]);
    }

    private static function mapIterator(iterable $iterator, callable $function): iterable
    {
        foreach ($iterator as $item) {
            yield $function($item);
        }
    }
}
