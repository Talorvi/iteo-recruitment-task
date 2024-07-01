<?php

namespace App\Tests\DTO;

use App\DTO\ProductDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDTOTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidProductDTO()
    {
        $productDTO = new ProductDTO('123', 10, 20.0, 5.0);
        $errors = $this->validator->validate($productDTO);

        $this->assertCount(0, $errors, "Expected no validation errors.");
    }

    public function testProductDTOWithNegativePrice()
    {
        $productDTO = new ProductDTO('123', 10, -1.0, 5.0);
        $errors = $this->validator->validate($productDTO);

        $this->assertGreaterThan(0, count($errors), "Expected validation errors for negative price.");
    }

    public function testProductDTOWithNegativeQuantity()
    {
        $productDTO = new ProductDTO('123', -5, 20.0, 5.0);
        $errors = $this->validator->validate($productDTO);

        $this->assertGreaterThan(0, count($errors), "Expected validation errors for negative quantity.");
    }

    public function testProductDTOWithNegativeWeight()
    {
        $productDTO = new ProductDTO('123', 10, 20.0, -1.0);
        $errors = $this->validator->validate($productDTO);

        $this->assertGreaterThan(0, count($errors), "Expected validation errors for negative weight.");
    }

    public function testProductDTOWithInvalidProductId()
    {
        $productDTO = new ProductDTO('', 10, 20.0, 5.0);
        $errors = $this->validator->validate($productDTO);

        $this->assertGreaterThan(0, count($errors), "Expected validation errors for blank product ID.");
    }
}
