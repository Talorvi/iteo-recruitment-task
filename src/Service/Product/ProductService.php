<?php

namespace App\Service\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProduct(string $productId): ?Product
    {
        return $this->productRepository->find($productId);
    }
}
