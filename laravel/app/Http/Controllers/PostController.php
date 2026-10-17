<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    private function notFound(string $entity, int $id): JsonResponse
    {
        return response()->json(['error' => "$entity with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    public function getPosts(): JsonResponse
    {
        return response()->json(['data' => Post::all()], Response::HTTP_OK);
    }

    public function getPostItem(int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->notFound('Post', $id);
        }

        return response()->json(['data' => $post], Response::HTTP_OK);
    }

    public function createPost(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['user_id']) || empty($data['title']) || empty($data['content'])) {
            return response()->json(['error' => 'user_id, title and content are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = User::find($data['user_id']);
        if (!$user) {
            return $this->notFound('User', $data['user_id']);
        }

        $post = Post::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'],
        ]);

        return response()->json(['data' => $post], Response::HTTP_CREATED);
    }

    public function updatePost(int $id, Request $request): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->notFound('Post', $id);
        }

        $data = $request->all();

        if (isset($data['title'])) {
            $post->title = $data['title'];
        }
        if (isset($data['content'])) {
            $post->content = $data['content'];
        }

        $post->save();

        return response()->json(['data' => $post], Response::HTTP_OK);
    }

    public function deletePost(int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->notFound('Post', $id);
        }

        $post->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
