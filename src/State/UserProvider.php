<?php
declare(strict_types=1);

namespace App\State;

use App\Entity\User;
use App\Service\UserService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;

class UserProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        // GetCollection → findAllUsers()
        if ($operation instanceof GetCollection) {
            return $this->userService->findAllUsers();
        }

        // Get → findUserById()
        return $this->userService->findUserById((int) $uriVariables['id']);
    }
}