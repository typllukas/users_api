<?php

namespace App\MessageHandler;

use App\Message\AuditLogMessage;
use App\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AuditLogMessageHandler
{
    public function __construct(private EntityManagerInterface $em) {}

    public function __invoke(AuditLogMessage $message): void
    {
        $changedData = [];

        if ($message->action === 'update' && $message->oldData && $message->newData) {
            foreach ($message->newData as $key => $newValue) {
                $oldValue = $message->oldData[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changedData[$key] = ['old' => $oldValue, 'new' => $newValue];
                }
            }
        } elseif ($message->action === 'create') {
            $changedData = array_map(fn($value) => ['old' => null, 'new' => $value], $message->newData ?? []);
        } elseif ($message->action === 'delete') {
            $changedData = array_map(fn($value) => ['old' => $value, 'new' => null], $message->oldData ?? []);
        }

        $log = (new AuditLog())
            ->setAction($message->action)
            ->setChangedData($changedData)
            ->setPerformedBy($message->performedById)
            ->setTargetUserId($message->targetUserId)
            ->setCreatedAt($message->timestamp);

        try {
            $this->em->persist($log);
            $this->em->flush();
        } catch (\Exception $e) {
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
}
