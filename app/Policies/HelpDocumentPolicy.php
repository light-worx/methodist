<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Lightworx\FilamentIssues\Models\HelpDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

class HelpDocumentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HelpDocument');
    }

    public function view(AuthUser $authUser, HelpDocument $helpDocument): bool
    {
        return $authUser->can('View:HelpDocument');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HelpDocument');
    }

    public function update(AuthUser $authUser, HelpDocument $helpDocument): bool
    {
        return $authUser->can('Update:HelpDocument');
    }

    public function delete(AuthUser $authUser, HelpDocument $helpDocument): bool
    {
        return $authUser->can('Delete:HelpDocument');
    }

    public function restore(AuthUser $authUser, HelpDocument $helpDocument): bool
    {
        return $authUser->can('Restore:HelpDocument');
    }

    public function forceDelete(AuthUser $authUser, HelpDocument $helpDocument): bool
    {
        return $authUser->can('ForceDelete:HelpDocument');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HelpDocument');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HelpDocument');
    }

    public function replicate(AuthUser $authUser, HelpDocument $helpDocument): bool
    {
        return $authUser->can('Replicate:HelpDocument');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HelpDocument');
    }

}