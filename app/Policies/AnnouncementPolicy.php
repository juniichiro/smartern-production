<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        // Admins can view everything
        if ($user->role_id === UserRole::ADMIN) {
            return true;
        }

        // Retrieve announcement creator details
        $creatorDetails = $announcement->user->supervisorDetails ?? null;

        // If there's no creator details (system / admin posts), let anyone view
        if (! $creatorDetails) {
            return true;
        }

        // Intern checks
        if ($user->role_id === UserRole::INTERN) {
            return $user->internDetails->office_id === $creatorDetails->office_id;
        }

        // Supervisor check - only in the same office
        if ($user->role_id === UserRole::SUPERVISOR) {
            return $user->supervisorDetails->office_id === $creatorDetails->office_id;
        }

        // Head supervisor can view all office announcements
        if ($user->role_id === UserRole::HEAD_SUPERVISOR) {
            return true;
        }

        // School coordinator can view all
        if ($user->role_id === UserRole::SCHOOL_COORDINATOR) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR, UserRole::SUPERVISOR]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        // Admin and Head Supervisor can edit all
        if (in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR])) {
            return true;
        }

        // Supervisors can only edit their own
        return $user->role_id === UserRole::SUPERVISOR && $user->id === $announcement->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        // Admin and Head Supervisor can delete all
        if (in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR])) {
            return true;
        }

        // Supervisors can only delete their own
        return $user->role_id === UserRole::SUPERVISOR && $user->id === $announcement->user_id;
    }
}
