<?php

namespace App\Service\Client;

use App\DTO\ClientDTO;
use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class ClientService
{
    private EntityManagerInterface $entityManager;
    private ClientRepository $clientRepository;

    public function __construct(EntityManagerInterface $entityManager, ClientRepository $clientRepository)
    {
        $this->entityManager = $entityManager;
        $this->clientRepository = $clientRepository;
    }

    public function getClient(string $clientId): ?Client
    {
        return $this->clientRepository->find($clientId);
    }

    public function createClient(ClientDTO $clientDTO): void
    {
        $client = new Client();

        $clientId = $clientDTO->getClientId() ?? Uuid::v4();

        $client->setClientId($clientId);
        $client->setName($clientDTO->getName());
        $client->setBalance($clientDTO->getBalance());

        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    public function subtractFromClientBalance(string $clientId, float $totalCost): void
    {
        $client = $this->getClient($clientId);
        if ($client) {
            $newBalance = $client->getBalance() - $totalCost;
            $client->setBalance($newBalance);
            $this->entityManager->persist($client);
        }
    }
}
