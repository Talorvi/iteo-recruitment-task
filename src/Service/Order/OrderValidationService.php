<?php

namespace App\Service\Order;

use App\DTO\OrderDTO;
use App\Service\Client\ClientService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderValidationService
{
    private const int MIN_PRODUCTS = 5;
    private const float MAX_TOTAL_WEIGHT = 24000;

    private ValidatorInterface $validator;
    private ClientService $clientService;
    private OrderService $orderService;

    public function __construct(
        ValidatorInterface $validator,
        ClientService      $clientService,
        OrderService       $orderService
    )
    {
        $this->validator = $validator;
        $this->clientService = $clientService;
        $this->orderService = $orderService;
    }

    public function validateOrderDTO(OrderDTO $orderDTO): array
    {
        $errors = $this->validator->validate($orderDTO);
        if (count($errors) > 0) {
            return [(string)$errors];
        }

        // Additional business validation
        $businessErrors = $this->validateOrderBusinessRules($orderDTO);
        if (!empty($businessErrors)) {
            return $businessErrors;
        }

        return [];
    }

    private function validateOrderBusinessRules(OrderDTO $orderDTO): array
    {
        $errors = [];

        // Check if the total number of products is at least self::MIN_PRODUCTS
        $totalProducts = $this->orderService->calculateProductNumberTotal($orderDTO);
        if ($totalProducts < self::MIN_PRODUCTS) {
            $errors[] = sprintf('Order must consist of at least %d products.', self::MIN_PRODUCTS);
        }

        // Check if the total weight is less than self::MAX_TOTAL_WEIGHT
        $totalWeight = $this->orderService->calculateOrderTotalWeight($orderDTO);
        if ($totalWeight >= self::MAX_TOTAL_WEIGHT) {
            $errors[] = sprintf('Total weight of products must be less than %d.', self::MAX_TOTAL_WEIGHT);
        }

        // Check if the client balance is sufficient to cover the order total
        $clientId = $orderDTO->getClientId();
        $client = $this->clientService->getClient($clientId);
        if (!$client) {
            $errors[] = 'Client not found.';
            return $errors;
        }

        $clientBalance = $client->getBalance();
        $totalCost = $this->orderService->calculateOrderTotal($orderDTO);

        if ($clientBalance < $totalCost) {
            $errors[] = 'Client balance must be greater than or equal to the total order cost.';
        }

        return $errors;
    }
}
