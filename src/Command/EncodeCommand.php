<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\Dummy;
use App\Dto\DummyWithRelated;
use App\Dto\ProductEncodableDto;
use App\Entity\Product;
use Symfony\Component\JsonEncoder\DecoderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\JsonEncoder\EncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\TypeInfo\Type;

/**
 *
 */
#[AsCommand(name: 'app:encode')]
final class EncodeCommand extends Command
{
    private string $productFilename;

    public function __construct(
        private readonly EncoderInterface $encoder,

    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dummy = new Dummy();
        $dummy->name = 'tobias';
        $dummy->id = 4711;
        $dummy->price = 1234;

        $output->writeln($this->encoder->encode($dummy));
        // {"id":4711,"name":"tobias","price":1234}

        // -----------

        $dummyA = new DummyWithRelated();
        $dummyA->name = 'tobias';
        $dummyA->id = 4711;
        $dummyA->price = 1234;

        $dummyB = new DummyWithRelated();
        $dummyB->name = 'Mattias';
        $dummyB->id = 4712;
        $dummyB->price = 4321;
        $dummyB->related = $dummyA;

        $output->writeln($this->encoder->encode($dummyB));
        //   Max depth has been reached for class "App\Dto\DummyWithRelated" (configured limit: 32).

        return Command::SUCCESS;
    }
}
