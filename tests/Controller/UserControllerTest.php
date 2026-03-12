<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour la classe UserController :
 * - Simulent de vraies requêtes HTTP
 * - Testent l'API de bout en bout (Controller → Service → Repository → BDD)
 * - Les plus complets mais aussi les plus lents
 */
final class UserControllerTest extends WebTestCase
{
     private $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        $users = $this->userRepository->findAllUsers();
        foreach ($users as $user) {
            if ($user->getId() !== null) {
                $this->userRepository->deleteUser($user);
            }
        }
        parent::tearDown();
    }

    private function createTestUser(string $name = 'Alice', int $age = 30, string $email = 'alice@example.com'): User
    {
        $user = new User();
        $user->setName($name)->setAge($age)->setEmail($email);
        return $this->userRepository->createUser($user);
    }

    // =============================================
    // GET /api/users
    // =============================================

    public function testGetAllUsersReturnsCollection(): void
    {
        $this->createTestUser('Alice', 30, 'alice@example.com');
        $this->createTestUser('Bob', 25, 'bob@example.com');

        $this->client->request('GET', '/api/users', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame(2, $data['totalItems']);
    }

    // =============================================
    // GET /api/users/{id}
    // =============================================

    public function testGetUserByIdReturnsUser(): void
    {
        $user = $this->createTestUser();

        $this->client->request('GET', '/api/users/' . $user->getId(), [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Alice', $data['name']);
        $this->assertSame(30, $data['age']);
    }

    public function testGetUserByIdReturns404WhenNotFound(): void
    {
        $this->client->request('GET', '/api/users/9999', [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    // =============================================
    // POST /api/users
    // =============================================

    public function testPostUserCreatesUser(): void
    {
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            [
                'HTTP_ACCEPT'   => 'application/ld+json',
                'CONTENT_TYPE'  => 'application/ld+json',
            ],
            json_encode([
                'name'  => 'Charlie',
                'age'   => 28,
                'email' => 'charlie@example.com',
            ])
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('Charlie', $data['name']);
        $this->assertSame(28, $data['age']);
    }

    // =============================================
    // DELETE /api/users/{id}
    // =============================================

    public function testDeleteUserReturns204(): void
    {
        $user = $this->createTestUser();
        $id = $user->getId();

        $this->client->request('DELETE', '/api/users/' . $id, [], [], [
            'HTTP_ACCEPT' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(204);

        $deleted = $this->userRepository->findUserById($id);
        $this->assertNull($deleted);
    }
}
