<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    private const USERS = [
        ['id' => 1, 'name' => 'Anton Ivanov', 'email' => 'anton@example.com'],
        ['id' => 2, 'name' => 'Kateryna Snihur', 'email' => 'kateryna@example.com'],
        ['id' => 3, 'name' => 'Andrii Boiko', 'email' => 'andrii@example.com'],
    ];

    private function findUser(int $id): ?array
    {
        foreach (self::USERS as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => "User with id $id not found"]], Response::HTTP_NOT_FOUND);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/users', name: 'get_users', methods: [Request::METHOD_GET])]
    public function getUsers(): JsonResponse
    {
        return new JsonResponse(['data' => self::USERS], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/users/{id}', name: 'get_user_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getUserItem(int $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (!$user) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $user], Response::HTTP_OK);
    }

    /**
     * @param $request Request
     * @return JsonResponse
     */
    #[Route('/users', name: 'create_user', methods: [Request::METHOD_POST])]
    public function createUser(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];
        if (empty($requestData['name']) || empty($requestData['email'])) {
            return new JsonResponse(['data' => ['error' => 'name and email are required']], Response::HTTP_BAD_REQUEST);
        }

        $newUserData = [
            'id' => max(array_column(self::USERS, 'id')) + 1,
            'name' => $requestData['name'],
            'email' => $requestData['email'],
        ];

        return new JsonResponse(['data' => $newUserData], Response::HTTP_CREATED);
    }

    /**
     * @param $id int
     * @param $request Request
     * @return JsonResponse
     */
    #[Route('/users/{id}', name: 'update_user', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->findUser($id);
        if (!$user) {
            return $this->notFound($id);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];
        $user['name'] = $requestData['name'] ?? $user['name'];
        $user['email'] = $requestData['email'] ?? $user['email'];

        return new JsonResponse(['data' => $user], Response::HTTP_OK);
    }

    /**
     * @param $id int
     * @return JsonResponse
     */
    #[Route('/users/{id}', name: 'delete_user', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (!$user) {
            return $this->notFound($id);
        }

        // TODO remove user from db
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
