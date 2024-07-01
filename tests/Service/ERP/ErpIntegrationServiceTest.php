<?php

namespace App\Tests\Service\ERP;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Service\ERP\ErpIntegrationService;
use App\Service\Order\OrderDtoConversionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Uid\Uuid;

class ErpIntegrationServiceTest extends TestCase
{
    private MockObject $httpClient;
    private MockObject $serializer;
    private ErpIntegrationService $erpIntegrationService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $erpUrl = 'https://erp.example.com';

        $conversionService = new OrderDtoConversionService();

        $this->erpIntegrationService = new ErpIntegrationService(
            $conversionService,
            $this->httpClient,
            $this->serializer,
            $erpUrl
        );
    }

    public function testSendOrderToERP()
    {
        // Create a new Order entity with related OrderProducts and Products
        $order = new Order();
        $order->setId(Uuid::v4());
        $order->setClientId(Uuid::v4());

        $product1 = new Product();
        $product1->setId(Uuid::v4());
        $product1->setPrice(100);
        $product1->setWeight(10);

        $orderProduct1 = new OrderProduct();
        $orderProduct1->setProduct($product1);
        $orderProduct1->setQuantity(2);
        $orderProduct1->setPrice(100);
        $orderProduct1->setWeight(10);

        $order->addOrderProduct($orderProduct1);

        $serializedOrder = '{"order": "serialized"}';

        // Mock the serializer
        $this->serializer->method('serialize')->willReturn($serializedOrder);

        // Mock the HTTP client response
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $this->httpClient->method('request')->willReturn($mockResponse);

        // Call the method and assert the result
        $result = $this->erpIntegrationService->sendOrderToERP($order);
        $this->assertTrue($result);
    }

    public function testSendOrderToERPWithException()
    {
        // Create a new Order entity
        $order = new Order();
        $order->setId(Uuid::v4());
        $order->setClientId(Uuid::v4());

        // Mock the serializer
        $serializedOrder = '{"order": "serialized"}';
        $this->serializer->method('serialize')->willReturn($serializedOrder);

        // Mock the HTTP client to throw an exception
        $this->httpClient->method('request')->willThrowException(new \Exception());

        // Call the method and assert the result
        $result = $this->erpIntegrationService->sendOrderToERP($order);
        $this->assertFalse($result);
    }
}
