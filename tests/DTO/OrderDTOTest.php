<?php

namespace App\Tests\DTO;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderDTOTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidOrderDTO()
    {
        $productDTO = new ProductDTO('001', 10, 100.0, 2.0);
        $orderId = Uuid::v4();
        $clientId = Uuid::v4();
        $orderDTO = new OrderDTO($orderId, $clientId, [$productDTO]);

        $errors = $this->validator->validate($orderDTO);
        $this->assertCount(0, $errors, "Expected no validation errors for a valid OrderDTO.");
    }

    public function testOrderDTOWithInvalidProduct()
    {
        $productDTO1 = new ProductDTO('001', -5, -100.0, -1.0); // Invalid product data
        $productDTO2 = new ProductDTO('002', 5, 150.0, 2.0);    // Valid product data
        $orderId = Uuid::v4();
        $clientId = Uuid::v4();
        $orderDTO = new OrderDTO($orderId, $clientId, [$productDTO1, $productDTO2]);

        $errors = $this->validator->validate($orderDTO);
        $this->assertGreaterThan(0, $errors->count(), "Expected validation errors at the order level because of invalid product.");

        // Iterate through each ProductDTO in the OrderDTO and validate it separately
        foreach ($orderDTO->getProductDTOs() as $index => $product) {
            $productErrors = $this->validator->validate($product);

            if ($product === $productDTO1) {
                $this->assertGreaterThan(0, $productErrors->count(), "Expected validation errors for ProductDTO1 at index $index.");
                foreach ($productErrors as $error) {
                    if ($error->getPropertyPath() === 'quantity') {
                        $this->assertStringContainsString('This value should be positive', $error->getMessage());
                    } elseif ($error->getPropertyPath() === 'price' || $error->getPropertyPath() === 'weight') {
                        $this->assertStringContainsString('This value should be either positive or zero', $error->getMessage());
                    }
                }
            } else if ($product === $productDTO2) {
                $this->assertCount(0, $productErrors, "No validation errors expected for ProductDTO2 at index $index.");
            }
        }
    }

    public function testOrderDTOWithEmptyProductsArray()
    {
        $orderId = Uuid::v4();
        $clientId = Uuid::v4();
        $orderDTO = new OrderDTO($orderId, $clientId, []);

        $errors = $this->validator->validate($orderDTO);
        $this->assertGreaterThan(0, $errors->count(), "Expected validation errors when products array is empty.");

        // Optionally, check for the specific error message
        $found = false;
        foreach ($errors as $error) {
            if ($error->getMessage() === "At least one product is required.") {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Expected specific error message for empty products array.");
    }
}
