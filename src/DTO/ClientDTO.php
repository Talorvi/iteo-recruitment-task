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
    #[Assert\Type('int')]
    #[Assert\PositiveOrZero]
    private int $balance;

    public function __construct(?Uuid $clientId, string $name, int $balance)
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

    /**
     * The amount is expressed as an integer without a currency unit.
     * For example, if a client has 55.26 in their account, the contract expects 5526.
     */
    public function getBalance(): int
    {
        return $this->balance;
    }
}
