<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:create-products',
    description: 'Generates a specified number of random products'
)]
class AppCreateProductsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('numProducts', InputArgument::OPTIONAL, 'The number of products to generate', 10)
            ->setDescription('Generates random products in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $numProducts = $input->getArgument('numProducts');

        for ($i = 0; $i < $numProducts; $i++) {
            $product = new Product();
            $product->setId(Uuid::v4());
            $product->setPrice(mt_rand(100, 1000) / 10.0);
            $product->setWeight(mt_rand(1, 100) / 10.0);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();
        $io->success("$numProducts products have been successfully generated.");

        return Command::SUCCESS;
    }
}

