<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserById(mixed $id): ?User
    {
        return $this->find($id);
    }

    public function findUserByName(string $name): ?User
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function createUser(User $user): User
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function deleteUser(User $user): void
    {
        if($this->findUserById($user->getId()) !== null) {    
            $this->getEntityManager()->remove($user);
            $this->getEntityManager()->flush();
         }
    }

    public function updateUser(User $user): User
    {
            if($this->findUserById($user->getId()) !== null) {    
                $this->getEntityManager()->flush();
            }
            return $user;
    }
}
