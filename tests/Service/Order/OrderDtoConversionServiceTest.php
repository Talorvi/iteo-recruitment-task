<?php

namespace App\Tests\Service\Order;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Service\Order\OrderDtoConversionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class OrderDtoConversionServiceTest extends TestCase
{
    public function testCreateOrderDTOFromOrder()
    {
        // Create a new Order entity
        $order = new Order();
        $order->setId(Uuid::v4());
        $order->setClientId(Uuid::v4());

        // Create Product entities
        $product1 = new Product();
        $product1->setId(Uuid::v4());
        $product1->setPrice(100);
        $product1->setWeight(10);

        $product2 = new Product();
        $product2->setId(Uuid::v4());
        $product2->setPrice(200);
        $product2->setWeight(20);

        // Create OrderProduct entities
        $orderProduct1 = new OrderProduct();
        $orderProduct1->setProduct($product1);
        $orderProduct1->setQuantity(2);
        $orderProduct1->setPrice(100);
        $orderProduct1->setWeight(10);

        $orderProduct2 = new OrderProduct();
        $orderProduct2->setProduct($product2);
        $orderProduct2->setQuantity(3);
        $orderProduct2->setPrice(200);
        $orderProduct2->setWeight(20);

        // Add OrderProduct entities to Order
        $order->addOrderProduct($orderProduct1);
        $order->addOrderProduct($orderProduct2);

        // Create the service and call the method under test
        $orderDtoConversionService = new OrderDtoConversionService();
        $orderDTO = $orderDtoConversionService->createOrderDTOFromOrder($order);

        // Assert that the returned OrderDTO matches the Order entity
        $this->assertInstanceOf(OrderDTO::class, $orderDTO);
        $this->assertEquals($order->getId(), $orderDTO->getOrderId());
        $this->assertEquals($order->getClientId(), $orderDTO->getClientId());

        // Assert that the OrderDTO contains the correct ProductDTOs
        $productDTOs = $orderDTO->getProductDTOs();
        $this->assertCount(2, $productDTOs);

        // Assert the first ProductDTO
        $this->assertEquals($product1->getId(), $productDTOs[0]->getProductId());
        $this->assertEquals(2, $productDTOs[0]->getQuantity());
        $this->assertEquals(100, $productDTOs[0]->getPrice());
        $this->assertEquals(10, $productDTOs[0]->getWeight());

        // Assert the second ProductDTO
        $this->assertEquals($product2->getId(), $productDTOs[1]->getProductId());
        $this->assertEquals(3, $productDTOs[1]->getQuantity());
        $this->assertEquals(200, $productDTOs[1]->getPrice());
        $this->assertEquals(20, $productDTOs[1]->getWeight());
    }
}
