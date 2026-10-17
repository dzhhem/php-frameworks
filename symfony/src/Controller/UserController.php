<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    private function notFound(int $id): JsonResponse
    {
        return $this->json(['error' => "User with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/users', name: 'get_users', methods: [Request::METHOD_GET])]
    public function getUsers(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->json(['data' => $users], Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'get_user_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getUserItem(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->notFound($id);
        }

        return $this->json(['data' => $user], Response::HTTP_OK);
    }

    #[Route('/users', name: 'create_user', methods: [Request::METHOD_POST])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'name, email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            return $this->json(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
        }

        $user = (new User())
            ->setName($data['name'])
            ->setEmail($data['email'])
            ->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['data' => $user], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'update_user', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            if ($this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
                return $this->json(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
            }
            $user->setEmail($data['email']);
        }
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }

        $this->entityManager->flush();

        return $this->json(['data' => $user], Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'delete_user', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->notFound($id);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
