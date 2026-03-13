<?php

namespace App\Repository;

use App\Entity\Article;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository implements ArticleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

   public function findArticleById(mixed $id): ?Article
    {
        return $this->find($id);
    }

    public function findArticlesByUser(int $userId): array
    {
        //return $this->findBy(['userId' => $userId]);
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function createArticle(Article $article): Article
    {
        $this->getEntityManager()->persist($article);
        $this->getEntityManager()->flush();
        return $article;
    }

    public function deleteArticle(Article $article): void
    {
        if ($this->findArticleById($article->getId()) !== null) {
            $this->getEntityManager()->remove($article);
            $this->getEntityManager()->flush();
        }
    }
}
