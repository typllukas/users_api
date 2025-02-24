<?php

namespace App\Enum;

enum DeleteMode: string
{
    case SOFT = 'soft';
    case HARD = 'hard';
}
