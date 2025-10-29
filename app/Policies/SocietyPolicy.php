<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Society;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocietyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Society');
    }

    public function view(AuthUser $authUser, Society $society): bool
    {
        return $authUser->can('View:Society');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Society');
    }

    public function update(AuthUser $authUser, Society $society): bool
    {
        return $authUser->can('Update:Society');
    }

    public function delete(AuthUser $authUser, Society $society): bool
    {
        return $authUser->can('Delete:Society');
    }

    public function restore(AuthUser $authUser, Society $society): bool
    {
        return $authUser->can('Restore:Society');
    }

    public function forceDelete(AuthUser $authUser, Society $society): bool
    {
        return $authUser->can('ForceDelete:Society');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Society');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Society');
    }

    public function replicate(AuthUser $authUser, Society $society): bool
    {
        return $authUser->can('Replicate:Society');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Society');
    }

}