<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\InvalidUserDataException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ValidatorInterface      $validator,
    ) {}

    // Méthode pour récupérer un utilisateur par ID
    public function findUserById(int $id): User
    {
        $user = $this->userRepository->findUserById($id);
        if (!$user) {
            throw new UserNotFoundException("Utilisateur avec l'ID $id introuvable.");
        }
        return $user;
    }

    // Méthode pour récupérer tous les utilisateurs
    public function findAllUsers(): array
    {
        return $this->userRepository->findAllUsers();
    }

    // Méthode pour créer un utilisateur
    public function createUser(string $name, int $age, string $email): User
    {
        $user = (new User())
            ->setName($name)
            ->setAge($age)
            ->setEmail($email);

        // ✅ Validation via les contraintes de l'entité User
        $this->validate($user);

        return $this->userRepository->createUser($user);
    }

    // Méthode pour mettre à jour un utilisateur
    public function updateUser(int $id, ?string $name, ?int $age, ?string $email): User
    {
        $user = $this->findUserById($id);

        if ($name !== null)  $user->setName($name);
        if ($age !== null)   $user->setAge($age);
        if ($email !== null) $user->setEmail($email);

        // ✅ Validation via les contraintes de l'entité User
        $this->validate($user);

        $this->userRepository->updateUser($user);
        return $user;
    }

    // Méthode pour désactiver un utilisateur
    public function deactivateUser(int $id): User
    {
        $user = $this->findUserById($id);
        $user->setIsActive(false);
        $this->userRepository->updateUser($user);
        return $user;
    }

    // Méthode pour supprimer un utilisateur
    public function deleteUser(int $id): void
    {
        $user = $this->findUserById($id);
        $this->userRepository->deleteUser($user);
    }

    // ✅ Méthode privée — valide l'entité et lance une exception si erreurs
    private function validate(User $user): void
    {
        $violations = $this->validator->validate($user);

        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new InvalidUserDataException(implode(' | ', $messages));
        }
    }
}