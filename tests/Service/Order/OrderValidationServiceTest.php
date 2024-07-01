<?php

namespace App\Tests\Service\Order;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\Service\Order\OrderValidationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderValidationServiceTest extends KernelTestCase
{
    private OrderValidationService $orderValidationService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->orderValidationService = static::getContainer()->get(OrderValidationService::class);
    }

    public function testValidateOrderDTOValid()
    {
        $productDTO = new ProductDTO('001', 1, 100.0, 10.0);
        $orderDTO = new OrderDTO(Uuid::v4(), Uuid::v4(), [$productDTO]);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(0, $errors);
    }

    public function testValidateOrderDTOInvalid()
    {
        $productDTO = new ProductDTO('', -1, -100.0, -10.0);
        $orderDTO = new OrderDTO(null, Uuid::v4(), [$productDTO]);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('This value should not be blank.', $errors[0]);
        $this->assertStringContainsString('This value should be positive.', $errors[0]);
    }

    public function testValidateOrderDTOWithNoProducts()
    {
        $orderDTO = new OrderDTO(Uuid::v4(), Uuid::v4(), []);

        $errors = $this->orderValidationService->validateOrderDTO($orderDTO);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('At least one product is required.', $errors[0]);
    }
}
