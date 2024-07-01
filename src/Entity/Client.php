<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'clients')]
class Client
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $clientId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'float')]
    private ?float $balance = null;

    public function getClientId(): ?Uuid
    {
        return $this->clientId;
    }

    public function setClientId(Uuid $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns Client balance without specified currency
     */
    public function getBalance(): ?float
    {
        return $this->balance;
    }

    /**
     * Sets Client balance without specified currency
     */
    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }
}
