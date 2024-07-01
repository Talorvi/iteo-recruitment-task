<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\OrderProduct;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testAddOrderProduct()
    {
        $order = new Order();
        $orderProduct = new OrderProduct();

        $this->assertCount(0, $order->getOrderProducts());

        $order->addOrderProduct($orderProduct);
        $this->assertCount(1, $order->getOrderProducts());
        $this->assertTrue($order->getOrderProducts()->contains($orderProduct));
        $this->assertSame($order, $orderProduct->getRelatedOrder());

        $order->addOrderProduct($orderProduct);
        $this->assertCount(1, $order->getOrderProducts());
    }

    public function testRemoveOrderProduct()
    {
        $order = new Order();
        $orderProduct = new OrderProduct();

        $order->addOrderProduct($orderProduct);
        $order->removeOrderProduct($orderProduct);
        $this->assertCount(0, $order->getOrderProducts());
        $this->assertNull($orderProduct->getRelatedOrder());
    }
}
