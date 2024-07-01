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

    public function __construct(ValidatorInterface $validator, ClientService $clientService)
    {
        $this->validator = $validator;
        $this->clientService = $clientService;
    }

    public function validateOrderDTO(OrderDTO $orderDTO): array
    {
        $errors = $this->validator->validate($orderDTO);
        if (count($errors) > 0) {
            return [(string) $errors];
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

        // Check if the order consists of self::MIN_PRODUCTS products
        if (count($orderDTO->getProductDTOs()) <= self::MIN_PRODUCTS) {
            $errors[] = sprintf('Order must consist of at least %d products.', self::MIN_PRODUCTS);
        }

        // Check if the total weight is less than self::MAX_TOTAL_WEIGHT
        $totalWeight = array_reduce($orderDTO->getProductDTOs(), function($carry, $productDTO) {
            return $carry + $productDTO->getWeight() * $productDTO->getQuantity();
        }, 0);
        if ($totalWeight >= self::MAX_TOTAL_WEIGHT) {
            $errors[] = sprintf('Total weight of products must be less than %d.', self::MAX_TOTAL_WEIGHT);
        }

        // Check if the client balance is positive
        $clientId = $orderDTO->getClientId();
        $clientBalance = $this->clientService->getClient($clientId)->getBalance();

        if ($clientBalance <= 0) {
            $errors[] = 'Client balance must be positive.';
        }

        return $errors;
    }
}
