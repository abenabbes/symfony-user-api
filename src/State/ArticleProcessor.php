<?php

declare(strict_types=1);

namespace App\State;

use App\Entity\Article;
use App\Service\ArticleService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Article
    {
        if ($operation instanceof Post) {
            try {
                return $this->articleService->createArticle(
                    (int) $uriVariables['userId'],
                    $data->getTitle(),
                    $data->getContent()
                );
            } catch (\Exception $e) {
                throw new NotFoundHttpException($e->getMessage());
            }
        }

        if ($operation instanceof Delete) {
            try {
                $this->articleService->deleteArticle((int) $uriVariables['id']);
            } catch (\RuntimeException $e) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return null;
        }

        return null;
    }
}