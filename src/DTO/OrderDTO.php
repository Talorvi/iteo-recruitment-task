<?php

namespace App\DTO;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class OrderDTO
{
    #[Assert\Uuid]
    private ?Uuid $orderId;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    private Uuid $clientId;

    /**
     * @var ProductDTO[]
     */
    #[Assert\All([
        new Assert\Type(type: "App\DTO\ProductDTO")
    ])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: "At least one product is required.")]
    private array $products;

    public function __construct(?Uuid $orderId, Uuid $clientId, array $products)
    {
        $this->orderId = $orderId;
        $this->clientId = $clientId;
        $this->products = $products;
    }

    public function getOrderId(): ?Uuid
    {
        return $this->orderId;
    }

    public function setOrderId(?Uuid $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getClientId(): Uuid
    {
        return $this->clientId;
    }

    public function setClientId(Uuid $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getProductDTOs(): array
    {
        return $this->products;
    }

    public function setProductDTOs(array $products): void
    {
        $this->products = $products;
    }
}
