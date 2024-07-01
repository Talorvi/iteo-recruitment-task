<?php

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
#[ORM\Table(name: '`order_products`')]
class OrderProduct
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $quantity = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?float $weight = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $relatedOrder = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

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
     * Gets OrderProduct weight in kilograms
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * Sets OrderProduct weight in kilograms
     */
    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    public function setRelatedOrder(?Order $relatedOrder): static
    {
        $this->relatedOrder = $relatedOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
