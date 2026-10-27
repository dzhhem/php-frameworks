<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController {
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param string $entity
     * @param int $id
     * @return JsonResponse
     */
    private function notFound(string $entity, int $id): JsonResponse
    {
        return $this->json(['error' => "$entity with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @return JsonResponse
     */
    #[Route('/posts', name: 'get_posts', methods: [Request::METHOD_GET])]
    public function getPosts(): JsonResponse
    {
        $posts = $this->entityManager->getRepository(Post::class)->findAll();

        return $this->json(['data' => $posts], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/posts/{id}', name: 'get_post_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getPostItem(int $id): JsonResponse
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);
        if (!$post) {
            return $this->notFound('Post', $id);
        }

        return $this->json(['data' => $post], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/posts', name: 'create_post', methods: [Request::METHOD_POST])]
    public function createPost(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['user_id']) || empty($data['title']) || empty($data['content'])) {
            return $this->json(['error' => 'user_id, title and content are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->notFound('User', $data['user_id']);
        }

        $post = (new Post())
            ->setUser($user)
            ->setTitle($data['title'])
            ->setContent($data['content']);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $this->json(['data' => $post], Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/posts/{id}', name: 'update_post', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updatePost(int $id, Request $request): JsonResponse
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);
        if (!$post) {
            return $this->notFound('Post', $id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $post], Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/posts/{id}', name: 'delete_post', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deletePost(int $id): JsonResponse
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);
        if (!$post) {
            return $this->notFound('Post', $id);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}