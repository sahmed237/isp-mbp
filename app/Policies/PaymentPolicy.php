<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if (!$user->can('payments.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $payment->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('payments.create');
    }
}
