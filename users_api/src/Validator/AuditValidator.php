<?php

namespace App\Validator;

use App\Exception\ValidationException;
use App\Utils\Validator;
use Symfony\Component\HttpFoundation\Request;

class AuditValidator extends Validator
{

    public function validateListParams(Request $request): array
    {
        $action = $request->query->get('action');
        $performedBy = $request->query->get('performed_by');
        $targetUserId = $request->query->get('target_user_id');
        $createdAfter = $request->query->get('created_after');
        $createdBefore = $request->query->get('created_before');

        if ($action) {
            $this->validateString('action', $action);
        }

        if ($performedBy) {
            $this->validateString('performed_by', $performedBy);
        }

        if ($targetUserId) {
            $this->validateString('target_user_id', $targetUserId);
        }

        $after = $createdAfter ? $this->validateDate('created_after', $createdAfter) : null;
        $before = $createdBefore ? $this->validateDate('created_before', $createdBefore) : null;

        if ($after && $before && $after > $before) {
            throw new ValidationException('created_after cannot be later than created_before.');
        }

        return compact('action', 'performedBy', 'targetUserId', 'createdAfter', 'createdBefore');
    }
}
