<?php

namespace App\Validator;

use App\Exception\ValidationException;
use App\Utils\Validator;
use Symfony\Component\HttpFoundation\Request;

class AuditValidator extends Validator
{

    public function validateListParams(Request $request): array
    {
        $allowedParams = ['action', 'performed_by_id', 'target_user_id', 'created_after', 'created_before'];

        $this->checkUnallowedParams($request->query->all(), $allowedParams);

        $action = $request->query->get('action');
        $performedById = $request->query->get('performed_by_id');
        $targetUserId = $request->query->get('target_user_id');
        $createdAfter = $request->query->get('created_after');
        $createdBefore = $request->query->get('created_before');

        if ($action) {
            $this->validateString('action', $action);
        }

        if ($performedById) {
            $this->validateStringToInteger('performed_by_id', $performedById);
        }

        if ($targetUserId) {
            $this->validateStringToInteger('target_user_id', $targetUserId);
        }

        // Append default times to 'created_after' and 'created_before' 
        if ($createdAfter) {
            $createdAfter .= ' 23:59:59';
        }
        if ($createdBefore) {
            $createdBefore .= ' 00:00:00';
        }

        $after = $createdAfter ? $this->validateDate('created_after',$createdAfter, 'Y-m-d H:i:s') : null;
        $before = $createdBefore ? $this->validateDate('created_before',$createdBefore, 'Y-m-d H:i:s') : null;

        if ($after && $before && $after > $before) {
            throw new ValidationException('created_after cannot be later than created_before.');
        }

        return compact('action', 'performedById', 'targetUserId', 'createdAfter', 'createdBefore');
    }
}
