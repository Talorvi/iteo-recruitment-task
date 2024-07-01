<?php

namespace App\Service\Client;

use App\Entity\Client;
use App\Repository\ClientRepository;

class ClientService
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getClient(string $clientId): ?Client
    {
        return $this->clientRepository->find($clientId);
    }
}
