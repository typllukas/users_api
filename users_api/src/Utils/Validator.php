<?php

namespace App\Utils;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class Validator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates JSON object
     */
    public function validateJsonObject(string $jsonContent): array
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

    /**
     * Validates a string with optional max length.
     */
    public function validateString(string $fieldName, $value, int $maxLength = 100): void
    {
        $violations = $this->validator->validate($value, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['max' => $maxLength])
        ]);

        $this->handleViolations($violations, $fieldName);
    }

    /**
     * Validates an email address format.
     */
    public function validateEmail(string $email): void
    {
        $violations = $this->validator->validate($email, [
            new Assert\NotBlank(),
            new Assert\Email()
        ]);

        $this->handleViolations($violations, 'email');
    }

    /**
     * Ensures all mandatory fields are provided
     */
    public function checkMandatoryFields(array $data, string|array $fields): void
    {
        $missingFields = [];

        foreach ((array) $fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $message = count($missingFields) > 1
                ? 'The following fields are mandatory: ' . implode(', ', $missingFields)
                : "{$missingFields[0]} is mandatory.";

            throw new ValidationException($message, ['missing_fields' => $missingFields]);
        }
    }

    /**
     * Validates date format and returns DateTime object.
     */
    public function validateDate(string $fieldName, string $date, string $format = 'Y-m-d'): \DateTime
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

    /**
     * Validates password strength.
     */
    public function validatePassword(string $password): void
    {
        $violations = $this->validator->validate($password, [
            new Assert\NotBlank(['message' => 'Password cannot be blank.']),
            new Assert\Length([
                'min' => 8,
                'minMessage' => 'Password must be at least {{ limit }} characters long.',
                'max' => 255
            ]),
            new Assert\Regex([
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Password must include uppercase, lowercase, number, and special character.'
            ])
        ]);

        $this->handleViolations($violations, 'password');
    }


    /**
     * Handles violations from Symfony Validator.
     */
    public function handleViolations(ConstraintViolationListInterface $violations, string $fieldName): void
    {
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$fieldName] = $violation->getMessage();
            }
            throw new ValidationException("Invalid input in {$fieldName}.", $errors);
        }
    }

    /**
     * Exposes Symfony Validator directly for custom validations.
     */
    public function validate($value, array $constraints): ConstraintViolationListInterface
    {
        return $this->validator->validate($value, $constraints);
    }
}
