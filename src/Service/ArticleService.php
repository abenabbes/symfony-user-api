<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use App\Repository\ArticleRepositoryInterface;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly UserRepositoryInterface    $userRepository,
    ) {}

    public function findArticleById(int $id): Article
    {
        $article = $this->articleRepository->findArticleById($id);
        if (!$article) {
            throw new \RuntimeException("Article avec l'ID $id introuvable.");
        }
        return $article;
    }

    public function findArticlesByUser(int $userId): array
    {
        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            throw new UserNotFoundException("Utilisateur avec l'ID $userId introuvable.");
        }
        return $this->articleRepository->findArticlesByUser($userId);
    }

    public function createArticle(int $userId, string $title, string $content): Article
    {
        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            throw new UserNotFoundException("Utilisateur avec l'ID $userId introuvable.");
        }

        $article = (new Article())
            ->setTitle($title)
            ->setContent($content)
            ->setUser($user);

        return $this->articleRepository->createArticle($article);
    }

    public function deleteArticle(int $id): void
    {
        $article = $this->findArticleById($id);
        $this->articleRepository->deleteArticle($article);
    }
}