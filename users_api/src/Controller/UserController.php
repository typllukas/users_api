<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Validator;
use App\Exception\ValidationException;
use App\Utils\JsonResponseHandler;

class UserController extends AbstractController
{
    use JsonResponseHandler;

    public function __construct(private Validator $validator) {}

    /* Single user info */
    #[Route('/api/users', name: 'user_show', methods: ['GET'])]
    public function show(Request $request): JsonResponse
    {
        $params = $this->validateShowParams($request);
        return $this->successResponse(['user' => $params]);
    }

    /* List of users based on specified filters */
    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = $this->validateListParams($request);
        return $this->successResponse(['users' => [], 'filters' => $filters]);
    }

    /* Create new user */
    #[Route('/api/users', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->validateCreateParams($request);
        return $this->successResponse(['status' => 'User created'], 201);
    }

    /* Update single user */
    #[Route('/api/users/{id}', name: 'user_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $updates = $this->validateUpdateParams($request);
        return $this->successResponse([
            'status' => "User with ID $id updated",
            'updated_fields' => $updates
        ]);
    }

    /* Delete single user */
    #[Route('/api/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return $this->successResponse(['status' => "User with ID $id deleted"]);
    }

    /* ------------------------
    ✅ Private Validation Methods
    ------------------------ */

    private function validateShowParams(Request $request): array
    {
        $id = $request->query->get('id');
        $email = $request->query->get('email');

        if ($id && $email) {
            throw new ValidationException('Specify either id or email, not both.');
        }

        if ($id) {
            $this->validator->validateString('id', $id);
            return ['id' => $id];
        }

        if ($email) {
            $this->validator->validateEmail($email);
            return ['email' => $email];
        }

        throw new ValidationException('Missing id or email parameter.');
    }

    private function validateListParams(Request $request): array
    {
        $role = $request->query->get('role');
        $createdAfter = $request->query->get('created_after');
        $createdBefore = $request->query->get('created_before');
        $includeDeleted = filter_var($request->query->get('include_deleted'), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted = filter_var($request->query->get('only_deleted'), FILTER_VALIDATE_BOOLEAN);

        if ($role) {
            $this->validator->validateRole($role);
        }

        $after = $createdAfter ? $this->validator->validateDate('created_after', $createdAfter) : null;
        $before = $createdBefore ? $this->validator->validateDate('created_before', $createdBefore) : null;

        if ($after && $before && $after > $before) {
            throw new ValidationException('created_after cannot be later than created_before.');
        }

        if ($includeDeleted && $onlyDeleted) {
            throw new ValidationException('Cannot set both include_deleted and only_deleted to true.');
        }

        return compact('role', 'createdAfter', 'createdBefore', 'includeDeleted', 'onlyDeleted');
    }

    private function validateCreateParams(Request $request): array
    {
        $data = $this->validator->validateJson($request->getContent());

        foreach (['name', 'email', 'password', 'role'] as $field) {
            $this->validator->checkMandatoryFields($data, $field);
            $this->validator->validateString($field, $data[$field]);
        }

        $this->validator->validateEmail($data['email']);
        $this->validator->validateRole($data['role']);

        return $data;
    }

    private function validateUpdateParams(Request $request): array
    {
        $data = $this->validator->validateJson($request->getContent());
        $updatableFields = ['name', 'email', 'password', 'role'];
        $validUpdates = [];

        foreach ($updatableFields as $field) {
            if (isset($data[$field])) {
                $this->validator->validateString($field, $data[$field]);

                if ($field === 'email') {
                    $this->validator->validateEmail($data[$field]);
                }

                if ($field === 'role') {
                    $this->validator->validateRole($data[$field]);
                }

                $validUpdates[$field] = $data[$field];
            }
        }

        if (empty($validUpdates)) {
            throw new ValidationException("At least one valid field must be provided for update.", [
                'valid_fields' => implode(', ', $updatableFields)
            ]);
        }

        return $validUpdates;
    }
}
