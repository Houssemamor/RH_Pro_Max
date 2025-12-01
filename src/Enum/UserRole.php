<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case HR_MANAGER = 'HR_MANAGER';
    case TEAM_MANAGER = 'TEAM_MANAGER';
    case EMPLOYEE = 'EMPLOYEE';
    case RECRUITER = 'RECRUITER';
}
