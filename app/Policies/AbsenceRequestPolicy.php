<?php

namespace App\Policies;

use App\Models\AbsenceRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsenceRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the absence request.
     */
    public function view(User $user, AbsenceRequest $absenceRequest): bool
    {
        return $user->id === $absenceRequest->intern_id ||
               in_array($user->role->name, ['supervisor', 'head_supervisor']);
    }

    /**
     * Determine whether the user can create absence requests.
     */
    public function create(User $user): bool
    {
        return $user->role->name === 'intern';
    }

    /**
     * Determine whether the user can update the absence request.
     */
    public function update(User $user, AbsenceRequest $absenceRequest): bool
    {
        return $user->id === $absenceRequest->intern_id && $absenceRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete the absence request.
     */
    public function delete(User $user, AbsenceRequest $absenceRequest): bool
    {
        return $user->id === $absenceRequest->intern_id && $absenceRequest->status === 'pending';
    }
}
