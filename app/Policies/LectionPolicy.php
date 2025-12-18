<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Lection;
use Illuminate\Auth\Access\HandlesAuthorization;

class LectionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lection');
    }

    public function view(AuthUser $authUser, Lection $lection): bool
    {
        return $authUser->can('View:Lection');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lection');
    }

    public function update(AuthUser $authUser, Lection $lection): bool
    {
        return $authUser->can('Update:Lection');
    }

    public function delete(AuthUser $authUser, Lection $lection): bool
    {
        return $authUser->can('Delete:Lection');
    }

    public function restore(AuthUser $authUser, Lection $lection): bool
    {
        return $authUser->can('Restore:Lection');
    }

    public function forceDelete(AuthUser $authUser, Lection $lection): bool
    {
        return $authUser->can('ForceDelete:Lection');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lection');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lection');
    }

    public function replicate(AuthUser $authUser, Lection $lection): bool
    {
        return $authUser->can('Replicate:Lection');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lection');
    }

}