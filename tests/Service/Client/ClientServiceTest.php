<?php

namespace App\Tests\Service\Client;

use App\DTO\ClientDTO;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\Client\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ClientServiceTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $clientRepository;
    private ClientService $clientService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->clientService = new ClientService($this->entityManager, $this->clientRepository);
    }

    public function testGetClient()
    {
        $clientId = Uuid::v4();
        $client = new Client();
        $client->setClientId($clientId);

        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with($clientId)
            ->willReturn($client);

        $result = $this->clientService->getClient($clientId->toRfc4122());
        $this->assertSame($client, $result);
    }

    public function testGetClientNotFound()
    {
        $clientId = Uuid::v4();

        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with($clientId)
            ->willReturn(null);

        $result = $this->clientService->getClient($clientId->toRfc4122());
        $this->assertNull($result);
    }

    public function testCreateClient()
    {
        $clientId = Uuid::v4();
        $clientDTO = new ClientDTO($clientId, 'Test Client', 100);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Client::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->clientService->createClient($clientDTO);
    }

    public function testSubtractFromClientBalance()
    {
        $clientId = Uuid::v4();
        $client = new Client();
        $client->setClientId($clientId);
        $client->setBalance(150.0);

        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with($clientId->toRfc4122())
            ->willReturn($client);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($client);

        $this->clientService->subtractFromClientBalance($clientId->toRfc4122(), 50.0);

        $this->assertEquals(100.0, $client->getBalance());
    }

    public function testSubtractFromClientBalanceClientNotFound()
    {
        $clientId = Uuid::v4();

        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with($clientId->toRfc4122())
            ->willReturn(null);

        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->clientService->subtractFromClientBalance($clientId->toRfc4122(), 50.0);
    }
}
