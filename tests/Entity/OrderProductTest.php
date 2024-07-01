<?php

namespace App\Tests\Entity;

use App\Entity\OrderProduct;
use App\Entity\Order;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class OrderProductTest extends TestCase
{
    public function testCreateOrderProduct()
    {
        $orderProduct = new OrderProduct();
        $order = new Order();
        $product = new Product();

        $product->setPrice(100.0);
        $product->setWeight(5.0);

        $orderProduct->setQuantity(10);
        $orderProduct->setPrice($product->getPrice());
        $orderProduct->setWeight($product->getWeight());
        $orderProduct->setRelatedOrder($order);
        $orderProduct->setProduct($product);

        $this->assertSame(10, $orderProduct->getQuantity());
        $this->assertSame($orderProduct->getPrice(), $product->getPrice()); // Asserting the Product's price
        $this->assertSame($orderProduct->getWeight(), $product->getWeight()); // Asserting the Product's weight
        $this->assertSame($order, $orderProduct->getRelatedOrder());
        $this->assertSame($product, $orderProduct->getProduct());
    }
}

