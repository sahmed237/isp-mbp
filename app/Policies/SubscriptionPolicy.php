<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('subscriptions.view');
    }

    public function view(User $user, Subscription $subscription): bool
    {
        if (!$user->can('subscriptions.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $subscription->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('subscriptions.create');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        if (!$user->can('subscriptions.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $subscription->organization_id;
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        if (!$user->can('subscriptions.cancel')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $subscription->organization_id;
    }
}
