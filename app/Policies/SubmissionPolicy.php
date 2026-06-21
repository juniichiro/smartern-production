<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Submission $submission): bool
    {
        // Intern who submitted it
        if ($user->role_id === UserRole::INTERN) {
            return $user->id === $submission->intern_id;
        }

        // Supervisor and Head Supervisor in the same office
        if (in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR])) {
            if ($user->supervisorDetails->office_id === $submission->task->office_id) {
                return true;
            }
        }

        return in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_id === UserRole::INTERN;
    }
}
