<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
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
    public function view(User $user, Attendance $attendance): bool
    {
        // Interns can view their own attendance
        if ($user->role_id === UserRole::INTERN) {
            return $user->id === $attendance->intern_id;
        }

        // Supervisors and Head Supervisors can view attendance in their own office
        if (in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR])) {
            if ($user->supervisorDetails->office_id === $attendance->office_id) {
                return true;
            }
        }

        // School Coordinators can view attendance for their students
        if ($user->role_id === UserRole::SCHOOL_COORDINATOR) {
            return $user->schoolCoordinatorDetails->school === $attendance->internDetails->school;
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
