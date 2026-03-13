<?php
declare(strict_types=1);

namespace App\State;

use App\Service\UserService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use App\Exception\UserNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


// c'est le [LECTEUR] de données pour les ressources User.
// Intervient sur les opérations GET
// Il récupère les données depuis le service et les retourne à API Platform
// API Platform se charge ensuite de les sérialiser en JSON-LD
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
        try {
            return $this->userService->findUserById((int) $uriVariables['id']);
        } catch (UserNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}