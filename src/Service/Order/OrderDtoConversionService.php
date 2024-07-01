<?php

namespace App\Service\Order;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\Entity\Order;

class OrderDtoConversionService
{
    public function createOrderDTOFromOrder(Order $order): OrderDTO
    {
        $productDTOs = [];
        foreach ($order->getOrderProducts() as $orderProduct) {
            $productDTOs[] = new ProductDTO(
                $orderProduct->getProduct()->getId(),
                $orderProduct->getQuantity(),
                $orderProduct->getPrice(),
                $orderProduct->getWeight()
            );
        }

        return new OrderDTO(
            $order->getId(),
            $order->getClientId(),
            $productDTOs
        );
    }
}
