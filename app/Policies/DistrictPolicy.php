<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\District;
use Illuminate\Auth\Access\HandlesAuthorization;

class DistrictPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:District');
    }

    public function view(AuthUser $authUser, District $district): bool
    {
        return $authUser->can('View:District');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:District');
    }

    public function update(AuthUser $authUser, District $district): bool
    {
        return $authUser->can('Update:District');
    }

    public function delete(AuthUser $authUser, District $district): bool
    {
        return $authUser->can('Delete:District');
    }

    public function restore(AuthUser $authUser, District $district): bool
    {
        return $authUser->can('Restore:District');
    }

    public function forceDelete(AuthUser $authUser, District $district): bool
    {
        return $authUser->can('ForceDelete:District');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:District');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:District');
    }

    public function replicate(AuthUser $authUser, District $district): bool
    {
        return $authUser->can('Replicate:District');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:District');
    }

}