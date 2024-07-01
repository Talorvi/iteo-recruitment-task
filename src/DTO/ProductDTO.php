<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private string $productId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    private int $quantity;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private float $price;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private float $weight;

    public function __construct(string $productId, int $quantity, float $price, float $weight)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->weight = $weight;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }
}
