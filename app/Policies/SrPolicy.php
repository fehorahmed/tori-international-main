<?php

namespace App\Policies;

use App\Enumeration\SupplierType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SrPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Usually handled by Spatie permissions (sr_management)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Supplier $sr): bool
    {
        // Superadmin can view everything
        if ($user->role == 'Superadmin') {
            return true;
        }

        // Must be in the same sister concern
        if ($user->sister_concern_id != $sr->sister_concern_id) {
            return false;
        }

        // If SR, can only view self
        if ($user->role == 'SR') {
            return $user->sr_id == $sr->id;
        }

        // Admin/User must have at least one SR related permission
        return $user->hasAnyPermission(['sr_tasks', 'sr_visits']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sr_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Supplier $sr): bool
    {
        // If SR, can only update self (if they have edit permission)
        if ($user->role == 'SR') {
            return $user->sr_id == $sr->id && $user->hasPermissionTo('sr_edit');
        }

        // Admin/Superadmin must have sr_edit permission and same sister concern
        return $user->hasPermissionTo('sr_edit') && $user->sister_concern_id == $sr->sister_concern_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Supplier $sr): bool
    {
        // Must have sr_delete permission and same sister concern
        return $user->hasPermissionTo('sr_delete') && $user->sister_concern_id == $sr->sister_concern_id;
    }
}
