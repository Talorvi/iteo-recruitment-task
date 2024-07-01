<?php

namespace App\DTO;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class ClientDTO
{
    #[Assert\Uuid]
    private ?Uuid $clientId;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\PositiveOrZero]
    private float $balance;

    public function __construct(?Uuid $clientId, string $name, float $balance)
    {
        $this->clientId = $clientId;
        $this->name = $name;
        $this->balance = $balance;
    }

    public function getClientId(): ?Uuid
    {
        return $this->clientId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}
