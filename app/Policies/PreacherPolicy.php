<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Preacher;
use Illuminate\Auth\Access\HandlesAuthorization;

class PreacherPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Preacher');
    }

    public function view(AuthUser $authUser, Preacher $preacher): bool
    {
        return $authUser->can('View:Preacher');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Preacher');
    }

    public function update(AuthUser $authUser, Preacher $preacher): bool
    {
        return $authUser->can('Update:Preacher');
    }

    public function delete(AuthUser $authUser, Preacher $preacher): bool
    {
        return $authUser->can('Delete:Preacher');
    }

    public function restore(AuthUser $authUser, Preacher $preacher): bool
    {
        return $authUser->can('Restore:Preacher');
    }

    public function forceDelete(AuthUser $authUser, Preacher $preacher): bool
    {
        return $authUser->can('ForceDelete:Preacher');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Preacher');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Preacher');
    }

    public function replicate(AuthUser $authUser, Preacher $preacher): bool
    {
        return $authUser->can('Replicate:Preacher');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Preacher');
    }

}