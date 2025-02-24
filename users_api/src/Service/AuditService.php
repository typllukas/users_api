<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class AuditService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function log(string $action, ?User $performedBy, int $targetUserId, ?array $oldData = null, ?array $newData = null): void
    {
        $changedData = [];

        if ($action === 'update' && $oldData && $newData) {
            foreach ($newData as $key => $newValue) {
                $oldValue = $oldData[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changedData[$key] = ['old' => $oldValue, 'new' => $newValue];
                }
            }
        } elseif ($action === 'create') {
            $changedData = array_map(fn($value) => ['old' => null, 'new' => $value], $newData ?? []);
        } elseif ($action === 'delete') {
            $changedData = array_map(fn($value) => ['old' => $value, 'new' => null], $oldData ?? []);
        }

        // Fallback to User ID 1 if $performedBy is null
        if ($performedBy === null) {
            $performedBy = $this->em->getReference(User::class, 1); // Lazy-loaded reference
        }

        $log = (new AuditLog())
            ->setAction($action)
            ->setChangedData($changedData)
            ->setPerformedBy($performedBy)
            ->setTargetUserId($targetUserId)
            ->setCreatedAt(new \DateTimeImmutable());

        try {
            $this->em->persist($log);
            $this->em->flush();
        } catch (ORMException | \Exception $e) {
            $detailedMessage = sprintf(
                "Failed to log audit data. Original error: %s in %s on line %d. Trace: %s",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );

            throw new \RuntimeException($detailedMessage, 0, $e);
        }
    }



    /* List audit logs with optional filters */
    public function listAuditLogs(array $filters = []): array
    {
        $repo = $this->em->getRepository(AuditLog::class);
        $qb = $repo->createQueryBuilder('a');

        if (!empty($filters['action'])) {
            $qb->andWhere('a.action = :action')
                ->setParameter('action', $filters['action']);
        }

        if (!empty($filters['performedBy'])) {
            $qb->andWhere('a.performedBy = :performedBy')
                ->setParameter('performedBy', $filters['performedBy']);
        }

        if (!empty($filters['targetUserId'])) {
            $qb->andWhere('a.targetUserId = :targetUserId')
                ->setParameter('targetUserId', $filters['targetUserId']);
        }

        if (!empty($filters['createdAfter'])) {
            $qb->andWhere('a.createdAt > :after')
                ->setParameter('after', $filters['createdAfter']);
        }

        if (!empty($filters['createdBefore'])) {
            $qb->andWhere('a.createdAt < :before')
                ->setParameter('before', $filters['createdBefore']);
        }

        $qb->orderBy('a.createdAt', 'DESC');

        return array_map(fn($log) => $this->formatAuditLog($log), $qb->getQuery()->getResult());
    }

    private function formatAuditLog(AuditLog $log): array
    {
        return [
            'id' => $log->getId(),
            'action' => $log->getAction(),
            'performed_by' => $log->getPerformedBy()->getEmail(),
            'target_user_id' => $log->getTargetUserId(),
            'changed_data' => $log->getChangedData(),
            'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
