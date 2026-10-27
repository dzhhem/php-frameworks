<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @param string $entity
     * @param int $id
     * @return JsonResponse
     */
    private function notFound(string $entity, int $id): JsonResponse
    {
        return response()->json(['error' => "$entity with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @return JsonResponse
     */
    public function getUsers(): JsonResponse
    {
        return response()->json(['data' => User::all()], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function getUserItem(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFound('User', $id);
        }

        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return response()->json(['error' => 'name, email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return response()->json(['data' => $user], Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFound('User', $id);
        }

        $data = $request->all();

        if (isset($data['email']) && $data['email'] !== $user->email) {
            if (User::where('email', $data['email'])->exists()) {
                return response()->json(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
            }
            $user->email = $data['email'];
        }
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return response()->json(['data' => $user], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUser(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFound('User', $id);
        }

        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
