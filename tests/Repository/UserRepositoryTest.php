<?php

declare(strict_types=1);

namespace tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests d'intégration pour la classe UserRepository :
 * - Utilisent la vraie base symfony_user_api_test
 * - Pas de mock
 * - Plus lents (accès BDD)
 * - Testent que le Repository fonctionne avec MySQL
 */
class UserRepositoryTest extends KernelTestCase
{
    // Injecte le UserRepository depuis le conteneur de services
    private UserRepository $userRepository;

    // Configure la base de données de test avant chaque test
    protected function setUp(): void
    {
        self::bootKernel(); // Démarre le kernel pour accéder au conteneur de services
        $this->userRepository = static::getContainer()->get(UserRepository::class); // Récupère le UserRepository depuis le conteneur
    }

    // Nettoie la base de données après chaque test pour éviter les interférences entre les tests
    protected function tearDown(): void
    {
        // Nettoie la base après chaque test
        $users = $this->userRepository->findAllUsers();
        foreach ($users as $user) {
            $this->userRepository->deleteUser($user);
        }
        parent::tearDown();
    }

    // Teste la création d'un utilisateur et sa récupération par ID
    public function testCreateAndFindUser(): void
    {
        $user = new User();
        $user->setName('Alice');
        $user->setAge(30);
        $user->setEmail('alice@example.com');

        $created = $this->userRepository->createUser($user);

        $this->assertNotNull($created->getId());
        $this->assertSame('Alice', $created->getName());
        $this->assertSame(30, $created->getAge());
    }

    // Teste la récupération de tous les utilisateurs
    public function testFindAllUsers(): void
    {
        $user1 = (new User())->setName('Alice')->setAge(30)->setEmail('alice@example.com');
        $user2 = (new User())->setName('Bob')->setAge(25)->setEmail('bob@example.com');

        $this->userRepository->createUser($user1);
        $this->userRepository->createUser($user2);

        $users = $this->userRepository->findAllUsers();

        $this->assertCount(2, $users);
    }

    // Teste la récupération d'un utilisateur par ID et le cas où l'utilisateur n'existe pas    
    public function testFindUserByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->userRepository->findUserById(9999);

        $this->assertNull($result);
    }

    // Teste la suppression d'un utilisateur et la vérification que l'utilisateur n'existe plus après la suppression
    public function testDeleteUser(): void
    {
        $user = new User();
        $user->setName('Charlie');
        $user->setAge(28);
        $user->setEmail('charlie@example.com');

        $created = $this->userRepository->createUser($user);
        $id = $created->getId();

        $this->userRepository->deleteUser($created);

        $result = $this->userRepository->findUserById($id);
        $this->assertNull($result);
    }

    // Teste la récupération des utilisateurs actifs et vérifie que seuls les utilisateurs actifs sont retournés
    public function testFindActiveUsers(): void
    {
        $active = (new User())->setName('Alice')->setAge(30)->setEmail('alice@example.com');
        $inactive = (new User())->setName('Bob')->setAge(25)->setEmail('bob@example.com');
        $inactive->setIsActive(false);

        $this->userRepository->createUser($active);
        $this->userRepository->createUser($inactive);

        $activeUsers = $this->userRepository->findActiveUsers();

        $this->assertCount(1, $activeUsers);
        $this->assertSame('Alice', $activeUsers[0]->getName());
    }
}