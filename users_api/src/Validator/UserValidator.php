<?php

namespace App\Validator;

use App\Exception\ValidationException;
use App\Utils\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\UserRole;

class UserValidator extends Validator
{

    /* ------------------------
    Endpoint-specific validation
    ------------------------ */

    public function validateLoginParams(string $jsonContent): array
    {
        $data = $this->validateJsonObject($jsonContent);

        $this->checkMandatoryFields(
            $data,
            ['email', 'password']
        );
        $this->validateEmail($data['email']);

        return $data;
    }

    public function validateShowParams(Request $request): array
    {
        $id = $request->query->get('id');
        $email = $request->query->get('email');

        if ($id && $email) {
            throw new ValidationException('Specify either id or email, not both.');
        }

        if ($id) {
            $this->validateString('id', $id);
            return ['id' => $id];
        } elseif ($email) {
            $this->validateEmail($email);
            return ['email' => $email];
        }

        throw new ValidationException('Missing id or email parameter.');
    }

    public function validateListParams(Request $request): array
    {

        $allowedParams = ['role', 'created_after', 'created_before', 'include_deleted', 'only_deleted'];

        $this->checkUnallowedParams($request->query->all(), $allowedParams);

        $role = $request->query->get('role');
        $createdAfter = $request->query->get('created_after');
        $createdBefore = $request->query->get('created_before');
        $includeDeleted = filter_var($request->query->get('include_deleted'), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted = filter_var($request->query->get('only_deleted'), FILTER_VALIDATE_BOOLEAN);

        if ($role) {
            $this->validateRole($role);
        }

        $after = $createdAfter ? $this->validateDate('created_after', $createdAfter) : null;
        $before = $createdBefore ? $this->validateDate('created_before', $createdBefore) : null;

        if ($after && $before && $after > $before) {
            throw new ValidationException('created_after cannot be later than created_before.');
        }

        if ($includeDeleted && $onlyDeleted) {
            throw new ValidationException('Cannot set both include_deleted and only_deleted to true.');
        }

        return compact('role', 'createdAfter', 'createdBefore', 'includeDeleted', 'onlyDeleted');
    }

    public function validateCreateParams(Request $request): array
    {
        $data = $this->validateJsonObject($request->getContent());

        $this->checkMandatoryFields($data, ['name', 'email', 'password', 'role']);

        // Values validation
        $this->validateString('name', $data['name']);
        $this->validateEmail($data['email']);
        $this->validatePassword($data['password']);
        $data['role'] = $this->validateRole($data['role']); // Validate role and set is as Enum

        return $data;
    }


    public function validateUpdateParams(Request $request): array
    {
        $data = $this->validateJsonObject($request->getContent());
        $updatableFields = ['name', 'email', 'password', 'role'];
        $validUpdates = [];
        $invalidFields = [];

        foreach ($data as $field => $value) {
            if (!in_array($field, $updatableFields, true)) {
                $invalidFields[] = $field;
                continue;
            }

            $validUpdates[$field] = match ($field) {
                'name'     => $this->validateString($field, $value),
                'email'    => $this->validateEmail($value),
                'password' => $this->validatePassword($value),
                'role'     => $this->validateRole($value)
            };
        }

        if (!empty($invalidFields)) {
            throw new ValidationException("Invalid fields provided for update.", [
                'invalid_fields' => $invalidFields,
                'allowed_fields' => $updatableFields
            ]);
        }

        if (empty($validUpdates)) {
            throw new ValidationException("At least one valid field must be provided for update.", [
                'valid_fields' => $updatableFields
            ]);
        }

        return $validUpdates;
    }



    /* ------------------------
    Single params validation
    ------------------------ */

    public function validateRole(string $role): UserRole
    {
        try {
            // Create enum from value
            return UserRole::from($role);
        } catch (\ValueError $e) {
            $allowedRoles = array_map(fn($case) => $case->value, UserRole::cases());
            throw new ValidationException("Invalid role '{$role}'. Allowed roles are: " . implode(', ', $allowedRoles));
        }
    }
}
