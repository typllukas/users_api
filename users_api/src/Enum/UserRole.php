<?php

namespace App\Enum;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';

    public function toSymfonyRole(): string
    {
        return 'ROLE_' . $this->value;
    }
}
