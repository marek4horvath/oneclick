<?php

declare(strict_types=1);

namespace App\Enums\Security;

enum UserRole: string
{
    case ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_COMPANY_MANAGER = 'ROLE_COMPANY_MANAGER';
    case ROLE_COMPANY_USER = 'ROLE_COMPANY_USER';
    case ROLE_END_USER = 'ROLE_END_USER';
}
