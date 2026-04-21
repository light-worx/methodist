<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Idea;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Idea');
    }

    public function view(AuthUser $authUser, Idea $idea): bool
    {
        return $authUser->can('View:Idea');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Idea');
    }

    public function update(AuthUser $authUser, Idea $idea): bool
    {
        return $authUser->can('Update:Idea');
    }

    public function delete(AuthUser $authUser, Idea $idea): bool
    {
        return $authUser->can('Delete:Idea');
    }

    public function restore(AuthUser $authUser, Idea $idea): bool
    {
        return $authUser->can('Restore:Idea');
    }

    public function forceDelete(AuthUser $authUser, Idea $idea): bool
    {
        return $authUser->can('ForceDelete:Idea');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Idea');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Idea');
    }

    public function replicate(AuthUser $authUser, Idea $idea): bool
    {
        return $authUser->can('Replicate:Idea');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Idea');
    }

}