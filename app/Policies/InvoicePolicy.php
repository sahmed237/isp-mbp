<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if (!$user->can('invoices.view')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $invoice->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if (!$user->can('invoices.update')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $invoice->organization_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        if (!$user->can('invoices.delete')) {
            return false;
        }

        return $user->isSuperAdmin() || $user->organization_id === $invoice->organization_id;
    }
}
