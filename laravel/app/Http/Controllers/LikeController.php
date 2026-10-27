<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LikeController extends Controller
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
    public function getLikes(): JsonResponse
    {
        return response()->json(['data' => Like::all()], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function getLikeItem(int $id): JsonResponse
    {
        $like = Like::find($id);
        if (!$like) {
            return $this->notFound('Like', $id);
        }

        return response()->json(['data' => $like], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createLike(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['user_id']) || empty($data['post_id'])) {
            return response()->json(['error' => 'user_id and post_id are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = User::find($data['user_id']);
        if (!$user) {
            return $this->notFound('User', $data['user_id']);
        }
        $post = Post::find($data['post_id']);
        if (!$post) {
            return $this->notFound('Post', $data['post_id']);
        }

        if (Like::where('user_id', $data['user_id'])->where('post_id', $data['post_id'])->exists()) {
            return response()->json(['error' => 'Like already exists'], Response::HTTP_CONFLICT);
        }

        $like = Like::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return response()->json(['data' => $like], Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLike(int $id, Request $request): JsonResponse
    {
        $like = Like::find($id);
        if (!$like) {
            return $this->notFound('Like', $id);
        }

        $data = $request->all();

        if (isset($data['post_id'])) {
            $post = Post::find($data['post_id']);
            if (!$post) {
                return $this->notFound('Post', $data['post_id']);
            }

            if (Like::where('user_id', $like->user_id)->where('post_id', $post->id)->exists()) {
                return response()->json(['error' => 'Like already exists'], Response::HTTP_CONFLICT);
            }

            $like->post_id = $post->id;
        }

        $like->save();

        return response()->json(['data' => $like], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deleteLike(int $id): JsonResponse
    {
        $like = Like::find($id);
        if (!$like) {
            return $this->notFound('Like', $id);
        }

        $like->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
