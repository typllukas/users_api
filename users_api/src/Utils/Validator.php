<?php

namespace App\Utils;

use App\Exception\ValidationException;

class Validator
{
    public static function validateJson(string $jsonContent): array
    {
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new ValidationException('Invalid JSON format. Please provide JSON object.');
        }

        if (empty($data)) {
            throw new ValidationException('Request body cannot be empty.', ['json' => 'Empty payload.']);
        }

        return $data;
    }

    public static function validateString(string $fieldName, $value, int $maxLength = 100): void
    {
        $errors = [];

        if (!is_string($value)) {
            $errors[$fieldName] = 'Must be a string.';
        } elseif (strlen($value) > $maxLength) {
            $errors[$fieldName] = "Must not exceed {$maxLength} characters.";
        }

        if (!empty($errors)) {
            throw new ValidationException("Invalid input in {$fieldName}.", $errors);
        }
    }

    public static function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format.', ['email' => 'Enter a valid email address.']);
        }
    }

    public static function validateRole(string $role): void
    {
        $allowedRoles = ['user', 'admin'];
        if (!in_array($role, $allowedRoles, true)) {
            throw new ValidationException('Invalid role.', ['role' => 'Allowed values: user, admin.']);
        }
    }

    public static function checkMandatoryFields(array $data, string $fieldName): void
    {
        if (!isset($data[$fieldName])) {
            throw new ValidationException("{$fieldName} is required.", [$fieldName => 'This field cannot be empty.']);
        }
    }

    public static function validateDate(string $fieldName, string $date, string $format = 'Y-m-d'): \DateTime
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        $errors = \DateTime::getLastErrors();

        if (!$dateTime || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            throw new ValidationException("Invalid date format.", [
                $fieldName => "Use format {$format}."
            ]);
        }

        return $dateTime;
    }
}
