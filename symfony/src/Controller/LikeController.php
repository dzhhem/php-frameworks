<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LikeController extends AbstractController {
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    private function notFound(string $entity, int $id): JsonResponse
    {
        return $this->json(['error' => "$entity with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/likes', name: 'get_likes', methods: [Request::METHOD_GET])]
    public function getLikes(): JsonResponse
    {
        $likes = $this->entityManager->getRepository(Like::class)->findAll();

        return $this->json(['data' => $likes], Response::HTTP_OK);
    }

    #[Route('/likes/{id}', name: 'get_like_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getLikeItem(int $id): JsonResponse
    {
        $like = $this->entityManager->getRepository(Like::class)->find($id);
        if (!$like) {
            return $this->notFound('Like', $id);
        }

        return $this->json(['data' => $like], Response::HTTP_OK);
    }

    #[Route('/likes', name: 'create_like', methods: [Request::METHOD_POST])]
    public function createLike(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['user_id']) || empty($data['post_id'])) {
            return $this->json(['error' => 'user_id and post_id are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->notFound('User', $data['user_id']);
        }
        $post = $this->entityManager->getRepository(Post::class)->find($data['post_id']);
        if (!$post) {
            return $this->notFound('Post', $data['post_id']);
        }

        if ($this->entityManager->getRepository(Like::class)->findOneBy(['user' => $user, 'post' => $post])) {
            return $this->json(['error' => 'Like already exists'], Response::HTTP_CONFLICT);
        }

        $like = (new Like())
            ->setUser($user)
            ->setPost($post);

        $this->entityManager->persist($like);
        $this->entityManager->flush();

        return $this->json(['data' => $like], Response::HTTP_CREATED);
    }

    #[Route('/likes/{id}', name: 'update_like', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateLike(int $id, Request $request): JsonResponse
    {
        $like = $this->entityManager->getRepository(Like::class)->find($id);
        if (!$like) {
            return $this->notFound('Like', $id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['post_id'])) {
            $post = $this->entityManager->getRepository(Post::class)->find($data['post_id']);
            if (!$post) {
                return $this->notFound('Post', $data['post_id']);
            }

            if ($this->entityManager->getRepository(Like::class)->findOneBy(['user' => $like->getUser(), 'post' => $post])) {
                return $this->json(['error' => 'Like already exists'], Response::HTTP_CONFLICT);
            }

            $like->setPost($post);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $like], Response::HTTP_OK);
    }

    #[Route('/likes/{id}', name: 'delete_like', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteLike(int $id): JsonResponse
    {
        $like = $this->entityManager->getRepository(Like::class)->find($id);
        if (!$like) {
            return $this->notFound('Like', $id);
        }

        $this->entityManager->remove($like);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}