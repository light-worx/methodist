<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Circuit;
use Illuminate\Auth\Access\HandlesAuthorization;

class CircuitPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Circuit');
    }

    public function view(AuthUser $authUser, Circuit $circuit): bool
    {
        return $authUser->can('View:Circuit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Circuit');
    }

    public function update(AuthUser $authUser, Circuit $circuit): bool
    {
        return $authUser->can('Update:Circuit');
    }

    public function delete(AuthUser $authUser, Circuit $circuit): bool
    {
        return $authUser->can('Delete:Circuit');
    }

    public function restore(AuthUser $authUser, Circuit $circuit): bool
    {
        return $authUser->can('Restore:Circuit');
    }

    public function forceDelete(AuthUser $authUser, Circuit $circuit): bool
    {
        return $authUser->can('ForceDelete:Circuit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Circuit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Circuit');
    }

    public function replicate(AuthUser $authUser, Circuit $circuit): bool
    {
        return $authUser->can('Replicate:Circuit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Circuit');
    }

}