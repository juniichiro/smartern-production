<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtering usually happens in the controller/query
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Supervisors and Head Supervisors can view tasks in their own office
        if (in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR])) {
            if ($user->supervisorDetails->office_id === $task->office_id) {
                return true;
            }
        }

        // Interns can view tasks assigned to them
        if ($user->role_id === UserRole::INTERN) {
            return $user->id === $task->intern_id;
        }

        // Admin and Head Supervisor can view all (Head Supervisor as catch-all for other offices)
        return in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Cannot update if already completed (non-repudiation)
        if ($task->status->value === 'completed') {
            return false;
        }

        // Only the supervisor (or head supervisor acting as supervisor) who created the task can edit it
        return in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR]) && $user->id === $task->supervisor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Cannot delete if already completed (non-repudiation)
        if ($task->status->value === 'completed') {
            return false;
        }

        // Only the supervisor (or head supervisor acting as supervisor) who created the task can delete it
        return in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR]) && $user->id === $task->supervisor_id;
    }
}
