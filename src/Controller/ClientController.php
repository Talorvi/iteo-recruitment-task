<?php

namespace App\Controller;

use App\DTO\ClientDTO;
use App\Service\Client\ClientService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientController extends AbstractController
{
    private ClientService $clientService;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(ClientService $clientService, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->clientService = $clientService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/api/client', methods: ['POST'])]
    public function createClient(Request $request): Response
    {
        try {
            $clientDTO = $this->serializer->deserialize($request->getContent(), ClientDTO::class, 'json');
        } catch (Exception $e) {
            return $this->json(['errors' => 'Failed to deserialize request: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($clientDTO);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->clientService->createClient($clientDTO);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['errors' => 'Client ID already exists. Please use a unique Client ID.'], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['errors' => 'An unexpected error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['status' => 'Client created successfully']);
    }
}
