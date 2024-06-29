<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use Symfony\Component\Uid\Uuid;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testCreateClient()
    {
        $client = new Client();
        $uuid = Uuid::v4();

        $client->setClientId($uuid);
        $client->setName('Test Client');
        $client->setBalance(1000.0);

        $this->assertSame($uuid->toRfc4122(), $client->getClientId()->toRfc4122());
        $this->assertSame('Test Client', $client->getName());
        $this->assertSame(1000.0, $client->getBalance());
    }
}
