<?php

namespace App\Tests\Service\Order;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\Entity\Client;
use App\Service\Client\ClientService;
use App\Service\Order\OrderService;
use App\Service\Order\OrderValidationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

class OrderValidationServiceTest extends TestCase
{
    private MockObject $clientService;
    private MockObject $orderService;
    private OrderValidationService $orderValidationService;

    protected function setUp(): void
    {
        $validator = Validation::createValidatorBuilder()->getValidator();
        $this->clientService = $this->createMock(ClientService::class);
        $this->orderService = $this->createMock(OrderService::class);
        $this->orderValidationService = new OrderValidationService($validator, $this->clientService, $this->orderService);
    }

    public function testValidateOrderDTOValid()
    {
        $clientId = Uuid::v4();
        $client = new Client();
        $client->setBalance(10000.0);

        $this->clientService->method('getClient')->with($clientId)->willReturn($client);

        $productDTOs = [
            new ProductDTO('001', 1, 100.0, 10.0),
            new ProductDTO('002', 1, 200.0, 20.0),
            new ProductDTO('003', 1, 300.0, 30.0),
            new ProductDTO('004', 1, 400.0, 40.0),
            new ProductDTO('005', 1, 500.0, 50.0)
        ];
        $orderDTO = new OrderDTO(Uuid::v4(), $clientId, $productDTOs);

        $this->orderService->method('calculateOrderTotal')->with($orderDTO)->willReturn(1500.0);
        $this->orderService->method('calculateOrderTotalWeight')->with($orderDTO)->willReturn(150.0);
        $this->orderService->method('calculateProductNumberTotal')->with($orderDTO)->willReturn(5);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(0, $errors);
    }

    public function testValidateOrderDTOInvalidDueToInsufficientBalance()
    {
        $clientId = Uuid::v4();
        $client = new Client();
        $client->setBalance(100.0);

        $this->clientService->method('getClient')->with($clientId)->willReturn($client);

        $productDTOs = [
            new ProductDTO('001', 1, 100.0, 10.0),
            new ProductDTO('002', 1, 200.0, 20.0),
            new ProductDTO('003', 1, 300.0, 30.0),
            new ProductDTO('004', 1, 400.0, 40.0),
            new ProductDTO('005', 1, 500.0, 50.0)
        ];
        $orderDTO = new OrderDTO(Uuid::v4(), $clientId, $productDTOs);

        $this->orderService->method('calculateOrderTotal')->with($orderDTO)->willReturn(1500.0);
        $this->orderService->method('calculateOrderTotalWeight')->with($orderDTO)->willReturn(150.0);
        $this->orderService->method('calculateProductNumberTotal')->with($orderDTO)->willReturn(5);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(1, $errors);
        $this->assertContains('Client balance must be greater than or equal to the total order cost.', $errors);
    }

    public function testValidateOrderDTOInvalidDueToTooFewProducts()
    {
        $clientId = Uuid::v4();
        $client = new Client();
        $client->setBalance(1000.0);

        $this->clientService->method('getClient')->with($clientId)->willReturn($client);

        $productDTOs = [
            new ProductDTO('001', 1, 100.0, 10.0),
            new ProductDTO('002', 1, 200.0, 20.0),
            new ProductDTO('003', 1, 300.0, 30.0),
            new ProductDTO('004', 1, 400.0, 40.0)
        ];
        $orderDTO = new OrderDTO(Uuid::v4(), $clientId, $productDTOs);

        $this->orderService->method('calculateOrderTotal')->with($orderDTO)->willReturn(1000.0);
        $this->orderService->method('calculateOrderTotalWeight')->with($orderDTO)->willReturn(100.0);
        $this->orderService->method('calculateProductNumberTotal')->with($orderDTO)->willReturn(4);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(1, $errors);
        $this->assertContains('Order must consist of at least 5 products.', $errors);
    }

    public function testValidateOrderDTOInvalidDueToExcessiveWeight()
    {
        $clientId = Uuid::v4();
        $client = new Client();
        $client->setBalance(5000.0);

        $this->clientService->method('getClient')->with($clientId)->willReturn($client);

        $productDTOs = [
            new ProductDTO('001', 1, 100.0, 10000.0),
            new ProductDTO('002', 1, 200.0, 10000.0),
            new ProductDTO('003', 1, 300.0, 10000.0),
            new ProductDTO('004', 1, 400.0, 10000.0),
            new ProductDTO('005', 1, 500.0, 10000.0)
        ];
        $orderDTO = new OrderDTO(Uuid::v4(), $clientId, $productDTOs);

        $this->orderService->method('calculateOrderTotal')->with($orderDTO)->willReturn(1500.0);
        $this->orderService->method('calculateOrderTotalWeight')->with($orderDTO)->willReturn(50000.0);
        $this->orderService->method('calculateProductNumberTotal')->with($orderDTO)->willReturn(5);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(1, $errors);
        $this->assertContains('Total weight of products must be less than 24000.', $errors);
    }
}

