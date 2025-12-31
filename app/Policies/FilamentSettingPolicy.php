<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Lightworx\FilamentSettings\Models\FilamentSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilamentSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FilamentSetting');
    }

    public function view(AuthUser $authUser, FilamentSetting $filamentSetting): bool
    {
        return $authUser->can('View:FilamentSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FilamentSetting');
    }

    public function update(AuthUser $authUser, FilamentSetting $filamentSetting): bool
    {
        return $authUser->can('Update:FilamentSetting');
    }

    public function delete(AuthUser $authUser, FilamentSetting $filamentSetting): bool
    {
        return $authUser->can('Delete:FilamentSetting');
    }

    public function restore(AuthUser $authUser, FilamentSetting $filamentSetting): bool
    {
        return $authUser->can('Restore:FilamentSetting');
    }

    public function forceDelete(AuthUser $authUser, FilamentSetting $filamentSetting): bool
    {
        return $authUser->can('ForceDelete:FilamentSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FilamentSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FilamentSetting');
    }

    public function replicate(AuthUser $authUser, FilamentSetting $filamentSetting): bool
    {
        return $authUser->can('Replicate:FilamentSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FilamentSetting');
    }

}