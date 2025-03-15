<?php

namespace App\Message;

use DateTimeImmutable;

class AuditLogMessage
{
    public function __construct(
        private readonly string   $action,
        private readonly ?int     $performedById,
        private readonly int      $targetUserId,
        private readonly ?array   $oldData = null,
        private readonly ?array   $newData = null,
        private readonly DateTimeImmutable $timestamp = new DateTimeImmutable()
    ) {}
}