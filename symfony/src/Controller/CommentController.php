<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController {
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    private function notFound(string $entity, int $id): JsonResponse
    {
        return $this->json(['error' => "$entity with id $id not found"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/comments', name: 'get_comments', methods: [Request::METHOD_GET])]
    public function getComments(): JsonResponse
    {
        $comments = $this->entityManager->getRepository(Comment::class)->findAll();

        return $this->json(['data' => $comments], Response::HTTP_OK);
    }

    #[Route('/comments/{id}', name: 'get_comment_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getCommentItem(int $id): JsonResponse
    {
        $comment = $this->entityManager->getRepository(Comment::class)->find($id);
        if (!$comment) {
            return $this->notFound('Comment', $id);
        }

        return $this->json(['data' => $comment], Response::HTTP_OK);
    }

    #[Route('/comments', name: 'create_comment', methods: [Request::METHOD_POST])]
    public function createComment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['user_id']) || empty($data['post_id']) || empty($data['content'])) {
            return $this->json(['error' => 'user_id, post_id and content are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->notFound('User', $data['user_id']);
        }
        $post = $this->entityManager->getRepository(Post::class)->find($data['post_id']);
        if (!$post) {
            return $this->notFound('Post', $data['post_id']);
        }

        $comment = (new Comment())
            ->setUser($user)
            ->setPost($post)
            ->setContent($data['content']);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $this->json(['data' => $comment], Response::HTTP_CREATED);
    }

    #[Route('/comments/{id}', name: 'update_comment', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function updateComment(int $id, Request $request): JsonResponse
    {
        $comment = $this->entityManager->getRepository(Comment::class)->find($id);
        if (!$comment) {
            return $this->notFound('Comment', $id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['content'])) {
            $comment->setContent($data['content']);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $comment], Response::HTTP_OK);
    }

    #[Route('/comments/{id}', name: 'delete_comment', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteComment(int $id): JsonResponse
    {
        $comment = $this->entityManager->getRepository(Comment::class)->find($id);
        if (!$comment) {
            return $this->notFound('Comment', $id);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}