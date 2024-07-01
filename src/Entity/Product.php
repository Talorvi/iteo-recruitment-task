<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: '`products`')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true)]
    private ?string $id = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?float $weight = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets Product price without specified currency
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Sets Product price without specified currency
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Gets Product weight in kilograms
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * Sets Product weight in kilograms
     */
    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }
}
