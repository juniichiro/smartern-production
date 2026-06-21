<?php

namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed'; // Matches your existing controller string
    case REVISION = 'revision';   // Matches your existing controller string
    case SUBMITTED = 'submitted';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Verified',
            self::REVISION => 'Needs Revisions',
            self::SUBMITTED => 'Submitted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::COMPLETED => 'green',
            self::REVISION => 'red',
            self::SUBMITTED => 'blue',
        };
    }
}
