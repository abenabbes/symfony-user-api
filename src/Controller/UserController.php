<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\InvalidUserDataException;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/custom')]
#[OA\Tag(name: 'Users (Custom Controller)')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    // ========================================
    // PAGES WEB
    // ========================================

    #[Route('/users', name: 'app_user_list', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->userService->findAllUsers();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $user = $this->userService->findUserById($id);
        } catch (UserNotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    // ========================================
    // API JSON
    // ========================================

    #[Route('/users', name: 'api_user_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/custom/users',
        summary: 'Retourne la liste de tous les utilisateurs',
        responses: [
            new OA\Response(response: 200, description: 'Liste des utilisateurs')
        ]
    )]
    public function apiList(): JsonResponse
    {
        $users = $this->userService->findAllUsers();
        $data  = array_map(fn($u) => [
            'id'        => $u->getId(),
            'name'      => $u->getName(),
            'age'       => $u->getAge(),
            'email'     => $u->getEmail(),
            'isActive'  => $u->isActive(),
            'createdAt' => $u->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $users);
        return $this->json($data);
    }

    #[Route('/users/{id}', name: 'api_user_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/custom/users/{id}',
        summary: 'Retourne un utilisateur par son ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Utilisateur trouvé'),
            new OA\Response(response: 404, description: 'Utilisateur introuvable'),
        ]
    )]
    public function apiShow(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);
        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
        return $this->json([
            'id'        => $user->getId(),
            'name'      => $user->getName(),
            'age'       => $user->getAge(),
            'email'     => $user->getEmail(),
            'isActive'  => $user->isActive(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/users', name: 'api_user_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/custom/users',
        summary: 'Crée un nouvel utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'age', 'email'],
                properties: [
                    new OA\Property(property: 'name',  type: 'string',  example: 'Alice'),
                    new OA\Property(property: 'age',   type: 'integer', example: 30),
                    new OA\Property(property: 'email', type: 'string',  example: 'alice@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Utilisateur créé'),
            new OA\Response(response: 400, description: 'Données invalides'),
        ]
    )]
    public function apiCreate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'], $data['age'], $data['email'])) {
            return $this->json(['error' => 'Les champs name, age et email sont obligatoires.'], 400);
        }
        try {
            $user = $this->userService->createUser(
                $data['name'],
                (int) $data['age'],
                $data['email']
            );
        } catch (InvalidUserDataException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
        return $this->json([
            'id'        => $user->getId(),
            'name'      => $user->getName(),
            'age'       => $user->getAge(),
            'email'     => $user->getEmail(),
            'isActive'  => $user->isActive(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], 201);
    }

    #[Route('/users/{id}', name: 'api_user_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/custom/users/{id}',
        summary: 'Met à jour un utilisateur',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name',  type: 'string',  example: 'Alice Updated'),
                    new OA\Property(property: 'age',   type: 'integer', example: 31),
                    new OA\Property(property: 'email', type: 'string',  example: 'alice@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Utilisateur mis à jour'),
            new OA\Response(response: 404, description: 'Utilisateur introuvable'),
            new OA\Response(response: 400, description: 'Données invalides'),
        ]
    )]
    public function apiUpdate(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $user = $this->userService->updateUser(
                $id,
                $data['name']  ?? null,
                isset($data['age']) ? (int) $data['age'] : null,
                $data['email'] ?? null,
            );
        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (InvalidUserDataException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
        return $this->json([
            'id'        => $user->getId(),
            'name'      => $user->getName(),
            'age'       => $user->getAge(),
            'email'     => $user->getEmail(),
            'isActive'  => $user->isActive(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/users/{id}/deactivate', name: 'api_user_deactivate', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/custom/users/{id}/deactivate',
        summary: 'Désactive un utilisateur',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Utilisateur désactivé'),
            new OA\Response(response: 404, description: 'Utilisateur introuvable'),
        ]
    )]
    public function apiDeactivate(int $id): JsonResponse
    {
        try {
            $user = $this->userService->deactivateUser($id);
        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
        return $this->json([
            'id'       => $user->getId(),
            'name'     => $user->getName(),
            'isActive' => $user->isActive(),
        ]);
    }

    #[Route('/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/custom/users/{id}',
        summary: 'Supprime un utilisateur',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Utilisateur supprimé'),
            new OA\Response(response: 404, description: 'Utilisateur introuvable'),
        ]
    )]
    public function apiDelete(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
        return $this->json(null, 204);
    }
}