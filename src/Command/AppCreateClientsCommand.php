<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:create-clients',
    description: 'Creates a specified number of random clients.'
)]
class AppCreateClientsCommand extends Command
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
            ->addArgument('count', InputArgument::REQUIRED, 'The number of clients to create')
            ->setHelp('This command allows you to create a specified number of clients with random data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $input->getArgument('count');

        if (!is_numeric($count) || $count < 1) {
            $io->error('Please provide a valid number of clients to create.');
            return Command::FAILURE;
        }

        for ($i = 0; $i < $count; $i++) {
            $client = new Client();
            $client->setClientId(Uuid::v4());
            $client->setName('Client ' . $i);
            $client->setBalance(rand(100, 10000));

            $this->entityManager->persist($client);
        }

        $this->entityManager->flush();
        $io->success("Successfully created $count random clients.");

        return Command::SUCCESS;
    }
}
