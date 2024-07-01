<?php

namespace App\Controller;

use App\DTO\OrderDTO;
use App\Service\ERP\ErpIntegrationService;
use App\Service\Order\OrderService;
use App\Service\Order\OrderValidationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController extends AbstractController
{
    private OrderService $orderService;
    private ErpIntegrationService $erpService;
    private OrderValidationService $validationService;
    private SerializerInterface $serializer;

    public function __construct(
        OrderService $orderService,
        ErpIntegrationService $erpService,
        OrderValidationService $validationService,
        SerializerInterface $serializer
    )
    {
        $this->orderService = $orderService;
        $this->erpService = $erpService;
        $this->validationService = $validationService;
        $this->serializer = $serializer;
    }

    #[Route('/api/order', methods: ['POST'])]
    public function createOrder(Request $request): Response
    {
        try {
            $orderDTO = $this->serializer->deserialize($request->getContent(), OrderDTO::class, 'json');
        } catch (Exception $e) {
            return $this->json(['errors' => 'Failed to deserialize request: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validationService->validateOrderDTO($orderDTO);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Optionally - check here if ERP can handle the order - check if products are available

        $order = $this->orderService->createOrder($orderDTO->getOrderId(), $orderDTO->getClientId());

        try {
            $this->orderService->processOrderProducts($orderDTO, $order);
        } catch (Exception $e) {
            return $this->json(['errors' => 'Failed to process order products: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        // Send order to ERP - consider using asynchronous queues if the ERP response time is critical
        /*if (!$this->erpService->sendOrderToERP($order)) {
            return $this->json(['errors' => 'Failed to send order to ERP'], Response::HTTP_SERVICE_UNAVAILABLE);
        }*/

        $this->orderService->saveOrder($order);
        return $this->json(['status' => 'Order processed']);
    }
}
