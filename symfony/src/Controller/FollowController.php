<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FollowController extends AbstractController {
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    private function notFound(string $entity, int $id): JsonResponse
    {
        return $this->json(['error' => "$entity with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/follows', name: 'get_follows', methods: [Request::METHOD_GET])]
    public function getFollows(): JsonResponse
    {
        $follows = $this->entityManager->getRepository(Follow::class)->findAll();

        return $this->json(['data' => $follows], Response::HTTP_OK);
    }

    #[Route('/follows/{id}', name: 'get_follow_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getFollowItem(int $id): JsonResponse
    {
        $follow = $this->entityManager->getRepository(Follow::class)->find($id);
        if (!$follow) {
            return $this->notFound('Follow', $id);
        }

        return $this->json(['data' => $follow], Response::HTTP_OK);
    }

    #[Route('/follows', name: 'create_follow', methods: [Request::METHOD_POST])]
    public function createFollow(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['follower_id']) || empty($data['following_id'])) {
            return $this->json(['error' => 'follower_id and following_id are required'], Response::HTTP_BAD_REQUEST);
        }

        $follower = $this->entityManager->getRepository(User::class)->find($data['follower_id']);
        if (!$follower) {
            return $this->notFound('User', $data['follower_id']);
        }
        $following = $this->entityManager->getRepository(User::class)->find($data['following_id']);
        if (!$following) {
            return $this->notFound('User', $data['following_id']);
        }

        if ($this->entityManager->getRepository(Follow::class)->findOneBy(['follower' => $follower, 'following' => $following])) {
            return $this->json(['error' => 'Follow already exists'], Response::HTTP_CONFLICT);
        }

        $follow = (new Follow())
            ->setFollower($follower)
            ->setFollowing($following);

        $this->entityManager->persist($follow);
        $this->entityManager->flush();

        return $this->json(['data' => $follow], Response::HTTP_CREATED);
    }

    #[Route('/follows/{id}', name: 'update_follow', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateFollow(int $id, Request $request): JsonResponse
    {
        $follow = $this->entityManager->getRepository(Follow::class)->find($id);
        if (!$follow) {
            return $this->notFound('Follow', $id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['following_id'])) {
            $following = $this->entityManager->getRepository(User::class)->find($data['following_id']);
            if (!$following) {
                return $this->notFound('User', $data['following_id']);
            }

            if ($this->entityManager->getRepository(Follow::class)->findOneBy(['follower' => $follow->getFollower(), 'following' => $following])) {
                return $this->json(['error' => 'Follow already exists'], Response::HTTP_CONFLICT);
            }

            $follow->setFollowing($following);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $follow], Response::HTTP_OK);
    }

    #[Route('/follows/{id}', name: 'delete_follow', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteFollow(int $id): JsonResponse
    {
        $follow = $this->entityManager->getRepository(Follow::class)->find($id);
        if (!$follow) {
            return $this->notFound('Follow', $id);
        }

        $this->entityManager->remove($follow);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}