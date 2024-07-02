<?php

namespace App\Tests\Service\Order;

use App\DTO\OrderDTO;
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
use Symfony\Component\Uid\Uuid;

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

    /**
     * @throws Exception
     */
    public function testProcessOrderProducts()
    {
        $order = new Order();
        $productDTO1 = new ProductDTO('001', 1, 100, 10);
        $productDTO2 = new ProductDTO('002', 2, 150, 20);

        $product1 = new Product();
        $product1->setPrice(100);
        $product1->setWeight(10);

        $product2 = new Product();
        $product2->setPrice(150);
        $product2->setWeight(20);

        $this->productService->method('getProduct')->willReturnOnConsecutiveCalls($product1, $product2);

        $this->orderProductRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function($entity) use (&$persistedEntities) {
                $persistedEntities[] = $entity;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        $orderDTO = new OrderDTO(Uuid::v4(), Uuid::v4(), [$productDTO1, $productDTO2]);

        $this->orderService->processOrderProducts($orderDTO, $order);
        $this->orderService->saveOrder($order);

        $this->assertCount(2, $order->getOrderProducts());

        $orderProduct1 = $order->getOrderProducts()[0];
        $this->assertEquals(100, $orderProduct1->getPrice());
        $this->assertEquals(10, $orderProduct1->getWeight());
        $this->assertEquals(1, $orderProduct1->getQuantity());

        $orderProduct2 = $order->getOrderProducts()[1];
        $this->assertEquals(150, $orderProduct2->getPrice());
        $this->assertEquals(20, $orderProduct2->getWeight());
        $this->assertEquals(2, $orderProduct2->getQuantity());

        $this->assertCount(3, $persistedEntities);
        $this->assertInstanceOf(OrderProduct::class, $persistedEntities[0]);
        $this->assertInstanceOf(OrderProduct::class, $persistedEntities[1]);
        $this->assertInstanceOf(Order::class, $persistedEntities[2]);
    }

    public function testCalculateOrderTotal()
    {
        $productDTO1 = new ProductDTO('001', 1, 100, 10);
        $productDTO2 = new ProductDTO('002', 2, 150, 20);

        $orderDTO = new OrderDTO(Uuid::v4(), Uuid::v4(), [$productDTO1, $productDTO2]);

        $total = $this->orderService->calculateOrderTotal($orderDTO);

        $this->assertEquals(400, $total);
    }

    public function testCalculateOrderTotalWeight()
    {
        $productDTO1 = new ProductDTO('001', 1, 100, 10);
        $productDTO2 = new ProductDTO('002', 2, 150, 20);

        $orderDTO = new OrderDTO(Uuid::v4(), Uuid::v4(), [$productDTO1, $productDTO2]);

        $totalWeight = $this->orderService->calculateOrderTotalWeight($orderDTO);

        $this->assertEquals(50, $totalWeight);
    }

    public function testCalculateProductNumberTotal()
    {
        $productDTO1 = new ProductDTO('001', 1, 100, 10);
        $productDTO2 = new ProductDTO('002', 2, 150, 20);

        $orderDTO = new OrderDTO(Uuid::v4(), Uuid::v4(), [$productDTO1, $productDTO2]);

        $productNumberTotal = $this->orderService->calculateProductNumberTotal($orderDTO);

        $this->assertEquals(3, $productNumberTotal);
    }
}
