<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
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
    public function getComments(): JsonResponse
    {
        return response()->json(['data' => Comment::all()], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function getCommentItem(int $id): JsonResponse
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->notFound('Comment', $id);
        }

        return response()->json(['data' => $comment], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createComment(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['user_id']) || empty($data['post_id']) || empty($data['content'])) {
            return response()->json(['error' => 'user_id, post_id and content are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = User::find($data['user_id']);
        if (!$user) {
            return $this->notFound('User', $data['user_id']);
        }
        $post = Post::find($data['post_id']);
        if (!$post) {
            return $this->notFound('Post', $data['post_id']);
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => $data['content'],
        ]);

        return response()->json(['data' => $comment], Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateComment(int $id, Request $request): JsonResponse
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->notFound('Comment', $id);
        }

        $data = $request->all();

        if (isset($data['content'])) {
            $comment->content = $data['content'];
        }

        $comment->save();

        return response()->json(['data' => $comment], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deleteComment(int $id): JsonResponse
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->notFound('Comment', $id);
        }

        $comment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
