<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserModuleGrant;
use App\Models\UserOperationDenial;

class DeskAccess
{
    public const MODULES = [
        'front_office' => 'Front office',
        'pos' => 'Restaurant POS',
    ];

    public const OPERATIONS = [
        'payments' => 'Take payments',
        'cancel_bill' => 'Cancel a restaurant bill',
        'cancel_reservation' => 'Cancel a reservation',
        'remove_charge' => 'Remove a room charge',
        'remove_line' => 'Remove a restaurant line',
        'no_show' => 'Mark a no-show',
        'rates' => 'Change room rates',
        'menu' => 'Edit the menu',
        'settings' => 'Change POS settings',
    ];

    public function allowsModule(?User $user, string $module): bool
    {
        if (! $user || ! isset(self::MODULES[$module])) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isGuest()) {
            return false;
        }

        return UserModuleGrant::query()
            ->where('user_id', $user->id)
            ->where('module', $module)
            ->exists();
    }

    public function allowsOperation(?User $user, string $operation): bool
    {
        if (! $user || ! isset(self::OPERATIONS[$operation])) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isGuest()) {
            return false;
        }

        return ! UserOperationDenial::query()
            ->where('user_id', $user->id)
            ->where('operation', $operation)
            ->exists();
    }

    public function canRemoveOwned(?User $user, ?int $createdBy): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $createdBy && (int) $createdBy === (int) $user->id;
    }
}
