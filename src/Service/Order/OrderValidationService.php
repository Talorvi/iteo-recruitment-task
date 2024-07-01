<?php

namespace App\Service\Order;

use App\DTO\OrderDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderValidationService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateOrderDTO(OrderDTO $orderDTO): array
    {
        $errors = $this->validator->validate($orderDTO);
        if (count($errors) > 0) {
            return [(string) $errors];
        }
        return [];
    }
}
