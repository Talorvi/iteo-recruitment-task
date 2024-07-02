<?php

namespace App\Tests\DTO;

use App\DTO\ClientDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Uid\Uuid;

class ClientDTOTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidClientDTO()
    {
        $clientId = Uuid::v4();
        $clientDTO = new ClientDTO($clientId, 'Test Client', 100);

        $errors = $this->validator->validate($clientDTO);
        $this->assertCount(0, $errors, "Expected no validation errors for a valid ClientDTO.");
    }

    public function testClientDTOWithInvalidName()
    {
        $clientId = Uuid::v4();
        $clientDTO = new ClientDTO($clientId, '', 100);

        $errors = $this->validator->validate($clientDTO);
        $this->assertGreaterThan(0, $errors->count(), "Expected validation errors for empty name.");

        $found = false;
        foreach ($errors as $error) {
            if ($error->getPropertyPath() === 'name') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Expected validation error for name.");
    }

    public function testClientDTOWithNegativeBalance()
    {
        $clientId = Uuid::v4();
        $clientDTO = new ClientDTO($clientId, 'Test Client', -100);

        $errors = $this->validator->validate($clientDTO);
        $this->assertGreaterThan(0, $errors->count(), "Expected validation errors for negative balance.");

        $found = false;
        foreach ($errors as $error) {
            if ($error->getPropertyPath() === 'balance') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Expected validation error for balance.");
    }
}
