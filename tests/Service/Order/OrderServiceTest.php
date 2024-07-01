<?php

namespace App\Tests\Service\Order;

use App\DTO\ProductDTO;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Repository\OrderProductRepository;
use App\Service\Order\OrderService;
use App\Service\Product\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $orderProductRepository;
    private MockObject $productService;
    private OrderService $orderService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->orderProductRepository = $this->createMock(OrderProductRepository::class);
        $this->productService = $this->createMock(ProductService::class);
        $this->orderService = new OrderService($this->entityManager, $this->orderProductRepository, $this->productService);
    }

    /**
     * @throws Exception
     */
    public function testAddNewProductToOrder()
    {
        $order = new Order();
        $productDTO = new ProductDTO('001', 1, 100, 10);

        $product = new Product();
        $product->setPrice(100);
        $product->setWeight(10);

        $this->productService->method('getProduct')->willReturn($product);

        $this->orderProductRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function($entity) use (&$persistedEntities) {
                $persistedEntities[] = $entity;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->orderService->addProductDTOToOrder($order, $productDTO);
        $this->orderService->saveOrder($order);

        $this->assertCount(1, $order->getOrderProducts());

        $orderProduct = $order->getOrderProducts()->first();
        $this->assertEquals(100, $orderProduct->getPrice());
        $this->assertEquals(10, $orderProduct->getWeight());
        $this->assertEquals(1, $orderProduct->getQuantity());

        $this->assertCount(2, $persistedEntities);
        $this->assertInstanceOf(OrderProduct::class, $persistedEntities[0]);
        $this->assertInstanceOf(Order::class, $persistedEntities[1]);
    }

    public function testAddProductToOrderThrowsException()
    {
        $order = new Order();
        $productDTO = new ProductDTO('unknown_id', 1, 100, 10);

        $this->productService->method('getProduct')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Product not found');

        $this->orderService->addProductDTOToOrder($order, $productDTO);
    }

    /**
     * @throws Exception
     */
    public function testUpdateExistingProductInOrder()
    {
        $order = new Order();
        $productDTO = new ProductDTO('001', 2, 100, 10);
        $product = new Product();
        $orderProduct = new OrderProduct();
        $orderProduct->setProduct($product);
        $orderProduct->setQuantity(1);
        $orderProduct->setRelatedOrder($order);
        $order->addOrderProduct($orderProduct);

        $this->productService->method('getProduct')->willReturn($product);
        $this->orderProductRepository->method('findOneBy')->willReturn($orderProduct);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->orderService->addProductDTOToOrder($order, $productDTO);
        $this->orderService->saveOrder($order);

        $this->assertEquals(3, $orderProduct->getQuantity());
    }
}
