<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testCreateProduct()
    {
        $product = new Product();

        $product->setId('001');
        $product->setPrice(19.99);
        $product->setWeight(0.5);

        $this->assertSame('001', $product->getId());
        $this->assertSame(19.99, $product->getPrice());
        $this->assertSame(0.5, $product->getWeight());
    }
}
