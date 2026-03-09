<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    /**
     * Trouve un utilisateur par son ID.
     */
    public function findUserById(mixed $id): ?User;

    /**
     * Retourne tous les utilisateurs.
     * @return User[]
     */
    public function findAllUsers(): array;

    /**
     * Trouve un utilisateur par son nom.
     */
    public function findUserByName(string $name): ?User;

    /**
     * Trouve les utilisateurs actifs.
     * @return User[]
     */
    public function findActiveUsers(): array;

    /**
     * Sauvegarde un utilisateur (create ou update).
     */
    public function createUser(User $user): User;

    /**
     * Supprime un utilisateur.
     */
    public function deleteUser(User $user): void;

    /**
     * Supprime un utilisateur.
     */
    public function updateUser(User $user): User;
}