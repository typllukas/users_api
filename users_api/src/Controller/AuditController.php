<?php

namespace App\Controller;

use App\Service\AuditService;
use App\Validator\AuditValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\JsonResponseHandler;

#[Route('/api/audit', name: 'audit_')]
class AuditController extends AbstractController
{
    use JsonResponseHandler;

    public function __construct(
        private AuditService $auditService,
        private AuditValidator $auditValidator
        ) {}

    /* List audit logs with optional filters */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = $this->auditValidator->validateListParams($request);
        $logs = $this->auditService->listAuditLogs($filters);
        return $this->successResponse(['logs' => $logs]);
    }
}
