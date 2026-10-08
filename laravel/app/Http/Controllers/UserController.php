<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
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
        return response()->json(['data' => ['error' => "User with id $id not found"]], Response::HTTP_NOT_FOUND);
    }

    /**
     * @return JsonResponse
     */
    public function getUsers(): JsonResponse
    {
        return response()->json(['data' => self::USERS], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function getUserItem(int $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (!$user) {
            return $this->notFound($id);
        }

        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    /**
     * @param $request Request
     * @return JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];
        if (empty($requestData['name']) || empty($requestData['email'])) {
            return response()->json(['data' => ['error' => 'name and email are required']], Response::HTTP_BAD_REQUEST);
        }

        $newUserData = [
            'id' => max(array_column(self::USERS, 'id')) + 1,
            'name' => $requestData['name'],
            'email' => $requestData['email'],
        ];

        return response()->json(['data' => $newUserData], Response::HTTP_CREATED);
    }

    /**
     * @param $id int
     * @param $request Request
     * @return JsonResponse
     */
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->findUser($id);
        if (!$user) {
            return $this->notFound($id);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];
        $user['name'] = $requestData['name'] ?? $user['name'];
        $user['email'] = $requestData['email'] ?? $user['email'];

        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    /**
     * @param $id int
     * @return JsonResponse
     */
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->findUser($id);
        if (!$user) {
            return $this->notFound($id);
        }

        // TODO remove user from db
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
