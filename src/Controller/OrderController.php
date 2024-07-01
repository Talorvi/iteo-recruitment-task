<?php

namespace App\Controller;

use App\DTO\OrderDTO;
use App\Service\Client\ClientService;
use App\Service\ERP\ErpIntegrationService;
use App\Service\Order\OrderService;
use App\Service\Order\OrderValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController extends AbstractController
{
    private OrderService $orderService;
    private ClientService $clientService;
    private ErpIntegrationService $erpService;
    private OrderValidationService $validationService;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        OrderService $orderService,
        ClientService $clientService,
        ErpIntegrationService $erpService,
        OrderValidationService $validationService,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    )
    {
        $this->orderService = $orderService;
        $this->clientService = $clientService;
        $this->erpService = $erpService;
        $this->validationService = $validationService;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
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

        $this->entityManager->beginTransaction();

        try {
            $order = $this->orderService->createOrder($orderDTO->getOrderId(), $orderDTO->getClientId());
            $this->orderService->processOrderProducts($orderDTO, $order);
            $totalCost = $this->orderService->calculateOrderTotal($orderDTO);
            $this->clientService->subtractFromClientBalance($orderDTO->getClientId(), $totalCost);
            $this->orderService->saveOrder($order);

            // Send order to ERP - consider using asynchronous queues if the ERP response time is critical
            /*if (!$this->erpService->sendOrderToERP($order)) {
                $this->entityManager->rollback();
                return $this->json(['errors' => 'Failed to send order to ERP'], Response::HTTP_SERVICE_UNAVAILABLE);
            }*/

            $this->entityManager->commit();

            return $this->json(['status' => 'Order processed']);
        } catch (Exception $e) {
            $this->entityManager->rollback();
            return $this->json(['errors' => 'Failed to process order: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
