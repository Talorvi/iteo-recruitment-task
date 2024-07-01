<?php

namespace App\Service\Order;

use App\DTO\OrderDTO;
use App\DTO\ProductDTO;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Repository\OrderProductRepository;
use App\Service\Product\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Uid\Uuid;

class OrderService
{
    private EntityManagerInterface $entityManager;
    private OrderProductRepository $orderProductRepository;
    private ProductService $productService;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderProductRepository $orderProductRepository,
        ProductService $productService
    )
    {
        $this->entityManager = $entityManager;
        $this->orderProductRepository = $orderProductRepository;
        $this->productService = $productService;
    }

    public function createOrder(?string $orderId, string $clientId): Order
    {
        $order = new Order();
        $uuid = $orderId ? new Uuid($orderId) : Uuid::v4();
        $order->setId($uuid);
        $order->setClientId(new Uuid($clientId));

        $this->entityManager->persist($order);

        return $order;
    }

    /**
     * Adds a product to an order, either by updating the quantity of an existing order product
     * if the product is already part of the order, or by creating a new order product with the
     * current product details. This method ensures that each product's price and weight at the
     * time of ordering are captured, reflecting any potential changes in product pricing or attributes
     * since being listed.
     * @throws Exception
     */
    public function addProductDTOToOrder(Order $order, ProductDTO $productDTO): void
    {
        $product = $this->productService->getProduct($productDTO->getProductId());
        if (!$product) {
            throw new Exception("Product not found: " . $productDTO->getProductId());
        }

        $orderProduct = $this->orderProductRepository->findOneBy([
            'relatedOrder' => $order,
            'product' => $product
        ]);

        if ($orderProduct) {
            $orderProduct->setQuantity($orderProduct->getQuantity() + $productDTO->getQuantity());
        } else {
            $orderProduct = new OrderProduct();
            $orderProduct->setRelatedOrder($order);
            $orderProduct->setProduct($product);
            $orderProduct->setQuantity($productDTO->getQuantity());
            $orderProduct->setPrice($productDTO->getPrice());
            $orderProduct->setWeight($productDTO->getWeight());
            $order->addOrderProduct($orderProduct);
            $this->entityManager->persist($orderProduct);
        }
    }

    /**
     * @throws Exception
     */
    public function processOrderProducts(OrderDTO $orderDTO, Order $order): void
    {
        foreach ($orderDTO->getProductDTOs() as $productDTO) {
            $this->addProductDTOToOrder($order, $productDTO);
        }
    }

    public function saveOrder(Order $order): void
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}
