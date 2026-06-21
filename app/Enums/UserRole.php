<?php

namespace App\Enums;

enum UserRole: int
{
    case ADMIN = 1;
    case HEAD_SUPERVISOR = 2;
    case SUPERVISOR = 3;
    case SCHOOL_COORDINATOR = 4;
    case INTERN = 5;

    /**
     * Get the human-readable name of the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::HEAD_SUPERVISOR => 'Head Supervisor',
            self::SUPERVISOR => 'Supervisor',
            self::SCHOOL_COORDINATOR => 'School Coordinator',
            self::INTERN => 'Intern',
        };
    }
}
