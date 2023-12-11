<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This example shows how the new encoder could be integrated in the current Serializer
 * in that way, only one encoder will be responsible of JSON encoding/decoding.
 *
 * Please note that there will be no performance improvment by doing that
 * because the exact data structure isn't know.
 *
 * But, it allows developper to call directly the encoder is performance/streaming
 * is needed (see other examples)
 */
#[AsCommand(name: 'example:json-encoder-bridge')]
class ExampleJsonEncoderBridgeCommand extends Command
{
    private readonly EntityRepository $productRepository;
    private string $productFilename;

    public function __construct(
        EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        string $projectDir,
    ) {
        parent::__construct();

        $this->productRepository = $em->getRepository(Product::class);
        $this->productFilename = sprintf('%s/products.json', $projectDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->echoProductFromDatabaseUsingSerializerEncoder();
        $this->echoProductFromDatabaseUsingEncoderEncoder();

        $this->createProductsFromJsonUsingSerializerEncoder();
        $this->createProductsFromJsonUsingEncoderEncoder();

        return Command::SUCCESS;
    }

    private function echoProductFromDatabaseUsingSerializerEncoder(): void
    {
        $productsFromDatabase = $this->productRepository->findAll();

        // regular normalization and regular encoding are processed
        echo $this->serializer->serialize($productsFromDatabase, 'json');
    }

    private function echoProductFromDatabaseUsingEncoderEncoder(): void
    {
        $productsFromDatabase = $this->productRepository->findAll();

        // regular normalization is processed but the new JsonEncoderBridgeEncoder is handling encoding
        echo $this->serializer->serialize($productsFromDatabase, 'json_bridged');
    }

    private function createProductsFromJsonUsingSerializerEncoder(): void
    {
        // regular denormalization and regular decoding are processed
        $entities = $this->serializer->deserialize(file_get_contents($this->productFilename), Product::class.'[]', 'json');

        dump($entities);
    }

    private function createProductsFromJsonUsingEncoderEncoder(): void
    {
        // regular denormalization is processed but the new JsonEncoderBridgeEncoder is handling decoding
        $entities = $this->serializer->deserialize(file_get_contents($this->productFilename), Product::class.'[]', 'json_bridged');

        dump($entities);
    }
}
