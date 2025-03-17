<?php

namespace App\Message;

use DateTimeImmutable;

class AuditLogMessage
{
    public function __construct(
        public readonly string   $action,
        public readonly ?int     $performedById,
        public readonly int      $targetUserId,
        public readonly ?array   $oldData = null,
        public readonly ?array   $newData = null,
        public readonly DateTimeImmutable $timestamp = new DateTimeImmutable()
    ) {}
}