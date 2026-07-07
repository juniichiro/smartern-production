<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\InternDetail;
use App\Models\User;

class CertificatePolicy
{
    /**
     * Determine whether the user can view the model
     */
    public function view(User $user, Certificate $certificate): bool
    {
        // Intern can view their own certificate
        if ($user->role_id === UserRole::INTERN) {
            return $user->id === $certificate->intern_id;
        }

        // Supervisor and Head Supervisor can view certificates of interns under their office
        if (in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR])) {
            $intern = InternDetail::where('user_id', $certificate->intern_id)->first();
            if ($intern && $intern->office_id === $user->supervisorDetails->office_id) {
                return true;
            }
        }

        // Head Supervisor and Admin can view all
        return in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Determine whether the user can create models
     */
    public function create(User $user): bool
    {
        return in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR, UserRole::SUPERVISOR]);
    }

    /**
     * Determine whether the user can update the model
     */
    public function update(User $user, Certificate $certificate): bool
    {
        // Supervisor and Head Supervisor can update/endorse if it belongs to their office
        if (in_array($user->role_id, [UserRole::SUPERVISOR, UserRole::HEAD_SUPERVISOR])) {
            $intern = InternDetail::where('user_id', $certificate->intern_id)->first();
            if ($intern && $intern->office_id === $user->supervisorDetails->office_id) {
                return true;
            }
        }

        return in_array($user->role_id, [UserRole::ADMIN, UserRole::HEAD_SUPERVISOR]);
    }

    /**
     * Determine whether the user can delete the model
     */
    public function delete(User $user, Certificate $certificate): bool
    {
        return $user->role_id === UserRole::ADMIN;
    }
}
