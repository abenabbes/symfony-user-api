<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;


interface ArticleRepositoryInterface
{
    /**
     * Trouve un article par son ID.
     */
    public function findArticleById(mixed $id): ?Article;

    /**
     * Retourne tous les articles.
     * @return Article[]
     */
    //public function findAllArticles(): array;

    /**
     * Trouve les articles d'un utilisateur.
     * @return Article[]
     */
    public function findArticlesByUser(int $userId): array;

    /**
     * Sauvegarde un article (create ou update).
     */
    public function createArticle(Article $article): Article;

    /**
     * Supprime un article.
     */
    public function deleteArticle(Article $article): void;

    /**
     * Supprime un article.
     */
    //public function updateArticle(Article $article): Article;
}