<?php

namespace App\Service\ERP;

use App\Entity\Order;
use App\Service\Order\OrderDtoConversionService;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ErpIntegrationService
{
    private OrderDtoConversionService $conversionService;
    private HttpClientInterface $httpClient;
    private SerializerInterface $serializer;
    private string $erpUrl;

    public function __construct(
        OrderDtoConversionService $conversionService,
        HttpClientInterface       $httpClient,
        SerializerInterface       $serializer,
        string                    $erpUrl
    )
    {
        $this->conversionService = $conversionService;
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->erpUrl = $erpUrl;
    }

    public function sendOrderToERP(Order $order): bool
    {
        $orderDTO = $this->conversionService->createOrderDTOFromOrder($order);
        $serializedOrder = $this->serializer->serialize($orderDTO, 'json');

        try {
            $response = $this->httpClient->request('POST', $this->erpUrl . '/order', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $serializedOrder
            ]);

            return $response->getStatusCode() === 200;
        } catch (Exception|TransportExceptionInterface $e) {
            return false;
        }
    }
}
