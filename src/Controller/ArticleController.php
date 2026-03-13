<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserNotFoundException;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/custom')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    // GET /api/custom/users/{userId}/articles
    #[Route('/users/{userId}/articles', methods: ['GET'])]
    public function getArticlesByUser(int $userId): JsonResponse
    {
        try {
            $articles = $this->articleService->findArticlesByUser($userId);

            $data = array_map(fn($article) => [
                'id'        => $article->getId(),
                'title'     => $article->getTitle(),
                'content'   => $article->getContent(),
                'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'userId'    => $article->getUser()->getId(),
            ], $articles);

            return $this->json($data);

        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    // GET /api/custom/articles/{id}
    #[Route('/articles/{id}', methods: ['GET'])]
    public function getArticle(int $id): JsonResponse
    {
        try {
            $article = $this->articleService->findArticleById($id);

            return $this->json([
                'id'        => $article->getId(),
                'title'     => $article->getTitle(),
                'content'   => $article->getContent(),
                'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'userId'    => $article->getUser()->getId(),
            ]);

        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    // POST /api/custom/users/{userId}/articles
    #[Route('/users/{userId}/articles', methods: ['POST'])]
    public function createArticle(int $userId, Request $request): JsonResponse
    {
        try {
            $body = json_decode($request->getContent(), true);

            $article = $this->articleService->createArticle(
                $userId,
                $body['title'] ?? '',
                $body['content'] ?? ''
            );

            return $this->json([
                'id'        => $article->getId(),
                'title'     => $article->getTitle(),
                'content'   => $article->getContent(),
                'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'userId'    => $article->getUser()->getId(),
            ], Response::HTTP_CREATED);

        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}