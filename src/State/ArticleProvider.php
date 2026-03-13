<?php

declare(strict_types=1);

namespace App\State;

use App\Entity\Article;
use App\Service\ArticleService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleProvider implements ProviderInterface
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        if ($operation instanceof GetCollection) {
            try {
                return $this->articleService->findArticlesByUser((int) $uriVariables['userId']);
            } catch (\Exception $e) {
                throw new NotFoundHttpException($e->getMessage());
            }
        }

        try {
            return $this->articleService->findArticleById((int) $uriVariables['id']);
        } catch (\RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}