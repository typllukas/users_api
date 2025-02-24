<?php

namespace App\Controller;

use App\Service\UserService;
use App\Validator\UserValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Utils\JsonResponseHandler;

#[Route('/api/users', name: 'user_')]
class UserController extends AbstractController
{
    use JsonResponseHandler;

    public function __construct(
        private UserService $userService,
        private UserValidator $userValidator
    ) {}

    /* Logged-in user info */
    #[Route('/me', name: 'api_users_me', methods: ['GET'])]
    public function me(UserInterface $user): JsonResponse
    {
        $formattedUser = $this->userService->formatUser($user);
        return $this->successResponse($formattedUser);
    }

    /* Get user info by id or email */
    #[Route('', name: 'show', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        $params = $this->userValidator->validateShowParams($request);
        $user = $this->userService->showUser($params);
        return $this->successResponse($user);
    }

    /* List users with optional filters */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = $this->userValidator->validateListParams($request);
        $users = $this->userService->listUsers($filters);
        return $this->successResponse(['users' => $users]);
    }

    /* Create new user */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserInterface $user): JsonResponse
    {
        $data = $this->userValidator->validateCreateParams($request);
        $newUser = $this->userService->createUser($data, $user);
        return $this->successResponse(['status' => 'User created', 'user' => $newUser], 201);
    }

    /* Update user */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id, UserInterface $user): JsonResponse
    {
        $updates = $this->userValidator->validateUpdateParams($request);
        $updatedUser = $this->userService->updateUser($updates, $id, $user);
        return $this->successResponse(['status' => "User with ID $id updated", 'user' => $updatedUser]);
    }

    /* Delete user (soft or hard) */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id, UserInterface $user): JsonResponse
    {
        $hardDelete = filter_var($request->query->get('hard_delete', false), FILTER_VALIDATE_BOOLEAN);
        $this->userService->deleteUser($id, $hardDelete, $user);
        return $this->successResponse(['status' => "User deleted"]);
    }

    /* ------------------------
    For testing only
    ------------------------ */

    /* Create test user without authentication */
    #[Route('/test-create', name: 'test_create', methods: ['POST'])]
    #[Route(
        '/test-create',
        name: 'test_create',
        methods: ['POST']
    )]
    public function testCreate(Request $request): JsonResponse
    {
        try {
            $data = $this->userValidator->validateCreateParams($request);
            $newUser = $this->userService->createUser($data, null);

            return $this->successResponse(['status' => 'Test user created', 'user' => $newUser], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
}
