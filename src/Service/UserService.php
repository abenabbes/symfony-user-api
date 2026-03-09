<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\InvalidUserDataException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;


class UserService
{

    // construct() — pour injecter des dépendances (ex: UserRepository)
    public function __construct(
        private readonly UserRepositoryInterface $userRepository        
    )
    {}

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
        // ✅ Règles métier ici — pas dans le Controller !
        if (empty(trim($name))) {
            throw new InvalidUserDataException("Le nom ne peut pas être vide.");
        }

        if ($age <= 0 || $age > 150) {
            throw new InvalidUserDataException("L'âge doit être entre 1 et 150.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidUserDataException("L'email '$email' est invalide.");
        }

        $user = new User();
        $user->setName($name);
        $user->setAge($age);
        $user->setEmail($email);

        return $this->userRepository->createUser($user);
    }

    // Méthode pour mettre à jour un utilisateur
    public function updateUser(int $id, ?string $name, ?int $age,   ?string $email): User
    {
        $user = $this->findUserById($id); // Vérifie que l'utilisateur existe

        if ($name !== null) {
            if (empty(trim($name))) {
                throw new InvalidUserDataException("Le nom ne peut pas être vide.");
            }
            $user->setName($name);
        }

        if ($age !== null) {
            if ($age <= 0 || $age > 150) {
                throw new InvalidUserDataException("L'âge doit être entre 1 et 150.");
            }
            $user->setAge($age);
        }

        if ($email !== null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidUserDataException("L'email '$email' est invalide.");
            }
            $user->setEmail($email);
        }
        
        return $this->userRepository->updateUser($user);
    }

    // Méthode pour déactiver un utilisateur
    public function deactivateUser(int $id): User
    {
        $user = $this->findUserById($id);
        $user->setIsActive(false);
        $this->userRepository->updateUser($user);
        return $user;
    }   

    // Méthode pour supprimer un utilisateur
    public function deleteUser(User $user): void
    {
            $this->userRepository->deleteUser($user);
    }   
}