<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeeklyPlanRow;
use Illuminate\Auth\Access\HandlesAuthorization;

class WeeklyPlanRowPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_weekly::plan::row');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WeeklyPlanRow $weeklyPlanRow): bool
    {
        return $user->can('view_weekly::plan::row');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_weekly::plan::row');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WeeklyPlanRow $weeklyPlanRow): bool
    {
        return $user->can('update_weekly::plan::row');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WeeklyPlanRow $weeklyPlanRow): bool
    {
        return $user->can('delete_weekly::plan::row');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_weekly::plan::row');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, WeeklyPlanRow $weeklyPlanRow): bool
    {
        return $user->can('force_delete_weekly::plan::row');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_weekly::plan::row');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, WeeklyPlanRow $weeklyPlanRow): bool
    {
        return $user->can('restore_weekly::plan::row');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_weekly::plan::row');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, WeeklyPlanRow $weeklyPlanRow): bool
    {
        return $user->can('replicate_weekly::plan::row');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_weekly::plan::row');
    }
}
