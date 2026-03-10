<?php
declare(strict_types=1);

namespace App\State;

use App\Entity\User;
use App\Service\UserService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;

class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        // POST → createUser()
        if ($operation instanceof Post) {
            return $this->userService->createUser(
                $data->getName(),
                $data->getAge(),
                $data->getEmail(),
            );
        }

        // PUT → updateUser()
        if ($operation instanceof Put) {
            return $this->userService->updateUser(
                (int) $uriVariables['id'],
                $data->getName(),
                $data->getAge(),
                $data->getEmail(),
            );
        }

        // PATCH → deactivateUser()
        if ($operation instanceof Patch) {
            return $this->userService->deactivateUser(
                (int) $uriVariables['id'],
            );
        }

        // DELETE → deleteUser()
        if ($operation instanceof Delete) {
            $this->userService->deleteUser(
                (int) $uriVariables['id'],
            );
        }

        return null;
    }
}