<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\InvalidUserDataException;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Hérite de AbstractController — donne accès à render(), json(), redirect()...
final class UserController extends AbstractController
{
    // Injection du service UserRepositoryInterface — pas besoin de "use" grâce à l'autowiring
    public function __construct
        (
         private readonly UserService $userService
        )
    {
    }

    // ========================================
    // PAGES WEB — Retournent du HTML via Twig
    // ========================================

    //  Attribut #[Route] — plus de routing manuel dans index.php !
    #[Route('/users', name: 'app_user_list', methods: ['GET'])]
    // Retourne une Response — tout est objet HTTP
    public function index(): Response
    {
        // Recupère tous les utilisateurs via le service — pas de logique métier dans le Controller !
        $users = $this->userService->findAllUsers();
        // render() — génère le HTML via Twig
        return $this->render('user/index.html.twig', [
            'users' => $users, // variables passées à Twig
        ]);
    }

    // Affiche un utilisateur spécifique
    #[Route('/users/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $id): Response
    {
       Try {
            $user = $this->userService->findUserById($id);
        }catch (UserNotFoundException $exp){
            throw $this->createNotFoundException($exp->getMessage());
        }
        
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    // ========================================
    // API JSON — Retournent du JSON
    // ========================================

    #[Route('/api/users', name: 'api_user_list', methods: ['GET'])]
    public function apiList(): JsonResponse
    {
        $users = $this->userService->findAllUsers();

        $data = array_map(fn($u) => [
            'id'        => $u->getId(),
            'name'      => $u->getName(),
            'age'       => $u->getAge(),
            'email'     => $u->getEmail(),
            'isActive'  => $u->isActive(),
            'createdAt' => $u->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $users);

        return $this->json($data);
    }

    #[Route('/api/users/{id}', name: 'api_user_show', methods: ['GET'])]
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

    #[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
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

    #[Route('/api/users/{id}', name: 'api_user_update', methods: ['PUT'])]
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

    #[Route('/api/users/{id}/deactivate', name: 'api_user_deactivate', methods: ['PATCH'])]
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

    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function apiDelete(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id); // Vérifie que l'utilisateur existe avant de tenter de le supprimer
            $this->userService->deleteUser($user);
        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        return $this->json(null, 204);
    }

}
