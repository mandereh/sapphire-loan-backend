<?php

namespace App\Policies;

use App\Constants\Status;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LoanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
        return $user->hasPermissionTo('view-loan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Loan $loan): bool
    {
        //
        return ($user->id === $loan->user_id) || ($user->id === $loan->reffered_by_id) || $user->hasPermissionTo('review-loan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Loan $loan): bool
    {
        //
        return $user->hasPermissionTo('edit-user');
        // && ($user->id === $loan->reffered_by_id);
    }

     /**
     * Determine whether the user can upload auth to the model.
     */
    public function uploadAuth(User $user, Loan $loan): bool
    {
        //
        return $user->hasPermissionTo('edit-user') && ($user->id === $loan->reffered_by_id);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Loan $loan): bool
    {
        //
        return $user->hasPermissionTo('approve-loan') && $loan->status == Status::PENDING_APPROVAL;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Loan $loan): bool
    {
        //
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Loan $loan): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Loan $loan): bool
    // {
    //     //
    // }
}
