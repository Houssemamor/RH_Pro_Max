<?php

namespace App\Enum;

enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case PENDING = 'PENDING';
    case LOCKED = 'LOCKED';
}
