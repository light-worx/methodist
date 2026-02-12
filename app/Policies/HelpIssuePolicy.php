<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Lightworx\FilamentIssues\Models\HelpIssue;
use Illuminate\Auth\Access\HandlesAuthorization;

class HelpIssuePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HelpIssue');
    }

    public function view(AuthUser $authUser, HelpIssue $helpIssue): bool
    {
        return $authUser->can('View:HelpIssue');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HelpIssue');
    }

    public function update(AuthUser $authUser, HelpIssue $helpIssue): bool
    {
        return $authUser->can('Update:HelpIssue');
    }

    public function delete(AuthUser $authUser, HelpIssue $helpIssue): bool
    {
        return $authUser->can('Delete:HelpIssue');
    }

    public function restore(AuthUser $authUser, HelpIssue $helpIssue): bool
    {
        return $authUser->can('Restore:HelpIssue');
    }

    public function forceDelete(AuthUser $authUser, HelpIssue $helpIssue): bool
    {
        return $authUser->can('ForceDelete:HelpIssue');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HelpIssue');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HelpIssue');
    }

    public function replicate(AuthUser $authUser, HelpIssue $helpIssue): bool
    {
        return $authUser->can('Replicate:HelpIssue');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HelpIssue');
    }

}