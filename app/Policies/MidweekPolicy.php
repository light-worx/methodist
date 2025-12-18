<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Midweek;
use Illuminate\Auth\Access\HandlesAuthorization;

class MidweekPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Midweek');
    }

    public function view(AuthUser $authUser, Midweek $midweek): bool
    {
        return $authUser->can('View:Midweek');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Midweek');
    }

    public function update(AuthUser $authUser, Midweek $midweek): bool
    {
        return $authUser->can('Update:Midweek');
    }

    public function delete(AuthUser $authUser, Midweek $midweek): bool
    {
        return $authUser->can('Delete:Midweek');
    }

    public function restore(AuthUser $authUser, Midweek $midweek): bool
    {
        return $authUser->can('Restore:Midweek');
    }

    public function forceDelete(AuthUser $authUser, Midweek $midweek): bool
    {
        return $authUser->can('ForceDelete:Midweek');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Midweek');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Midweek');
    }

    public function replicate(AuthUser $authUser, Midweek $midweek): bool
    {
        return $authUser->can('Replicate:Midweek');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Midweek');
    }

}