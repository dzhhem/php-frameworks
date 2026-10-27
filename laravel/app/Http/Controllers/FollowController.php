<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FollowController extends Controller
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
    public function getFollows(): JsonResponse
    {
        return response()->json(['data' => Follow::all()], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function getFollowItem(int $id): JsonResponse
    {
        $follow = Follow::find($id);
        if (!$follow) {
            return $this->notFound('Follow', $id);
        }

        return response()->json(['data' => $follow], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createFollow(Request $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['follower_id']) || empty($data['following_id'])) {
            return response()->json(['error' => 'follower_id and following_id are required'], Response::HTTP_BAD_REQUEST);
        }

        $follower = User::find($data['follower_id']);
        if (!$follower) {
            return $this->notFound('User', $data['follower_id']);
        }
        $following = User::find($data['following_id']);
        if (!$following) {
            return $this->notFound('User', $data['following_id']);
        }

        if (Follow::where('follower_id', $follower->id)->where('following_id', $following->id)->exists()) {
            return response()->json(['error' => 'Follow already exists'], Response::HTTP_CONFLICT);
        }

        $follow = Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);

        return response()->json(['data' => $follow], Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFollow(int $id, Request $request): JsonResponse
    {
        $follow = Follow::find($id);
        if (!$follow) {
            return $this->notFound('Follow', $id);
        }

        $data = $request->all();

        if (isset($data['following_id'])) {
            $following = User::find($data['following_id']);
            if (!$following) {
                return $this->notFound('User', $data['following_id']);
            }

            if (Follow::where('follower_id', $follow->follower_id)->where('following_id', $following->id)->exists()) {
                return response()->json(['error' => 'Follow already exists'], Response::HTTP_CONFLICT);
            }

            $follow->following_id = $following->id;
        }

        $follow->save();

        return response()->json(['data' => $follow], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deleteFollow(int $id): JsonResponse
    {
        $follow = Follow::find($id);
        if (!$follow) {
            return $this->notFound('Follow', $id);
        }

        $follow->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
