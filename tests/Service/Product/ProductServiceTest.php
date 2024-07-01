<?php

namespace App\Tests\Service\Product;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductServiceTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidProduct()
    {
        $product = new Product();
        $product->setPrice(20.0); // Valid price
        $product->setWeight(10.0); // Valid weight

        $errors = $this->validator->validate($product);
        $this->assertCount(0, $errors); // Expecting no validation errors
    }

    public function testProductValidation()
    {
        $product = new Product();
        $product->setPrice(-10); // Intentionally invalid to test validation
        $product->setWeight(-5); // Intentionally invalid to test validation

        $errors = $this->validator->validate($product);
        $this->assertGreaterThan(0, count($errors)); // Expecting validation errors

        // Check for specific error messages
        foreach ($errors as $error) {
            $this->assertTrue(in_array($error->getPropertyPath(), ['price', 'weight']));
            $this->assertStringContainsString('This value should be either positive or zero.', $error->getMessage());
        }
    }

    public function testPriceValidation()
    {
        $product = new Product();
        $product->setPrice(-10); // Invalid price

        $errors = $this->validator->validate($product);
        $this->assertGreaterThan(0, count($errors)); // Expecting validation errors

        $found = false;
        foreach ($errors as $error) {
            if ($error->getPropertyPath() === 'price') {
                $this->assertStringContainsString('This value should be either positive or zero.', $error->getMessage());
                $found = true;
            }
        }
        $this->assertTrue($found, "No validation error message found for price.");
    }

    public function testWeightValidation()
    {
        $product = new Product();
        $product->setWeight(-5); // Invalid weight

        $errors = $this->validator->validate($product);
        $this->assertGreaterThan(0, count($errors)); // Expecting validation errors

        $found = false;
        foreach ($errors as $error) {
            if ($error->getPropertyPath() === 'weight') {
                $this->assertStringContainsString('This value should be either positive or zero.', $error->getMessage());
                $found = true;
            }
        }
        $this->assertTrue($found, "No validation error message found for weight.");
    }
}
