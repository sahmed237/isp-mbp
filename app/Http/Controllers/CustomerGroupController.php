<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerGroupController extends Controller
{
    public function index(Request $request): View
    {
        $selectedType = $request->query('type', 'all');

        $groups = [
            'corporate' => [
                'name' => 'Corporate & Enterprise',
                'description' => 'Dedicated links, multi-office leased lines, and corporate SLAs.',
                'badge' => 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20',
                'count' => Customer::where('customer_type', 'corporate')->count(),
                'active' => Customer::where('customer_type', 'corporate')->where('status', 'active')->count(),
            ],
            'individual' => [
                'name' => 'Individual & Residential',
                'description' => 'Home broadband, FTTH fibre, and family internet subscribers.',
                'badge' => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
                'count' => Customer::where('customer_type', 'individual')->count(),
                'active' => Customer::where('customer_type', 'individual')->where('status', 'active')->count(),
            ],
            'government' => [
                'name' => 'Government & Parastatal',
                'description' => 'Ministries, departments, state agencies, and public institutions.',
                'badge' => 'bg-amber-500/10 text-amber-500 border border-amber-500/20',
                'count' => Customer::where('customer_type', 'government')->count(),
                'active' => Customer::where('customer_type', 'government')->where('status', 'active')->count(),
            ],
            'reseller' => [
                'name' => 'Reseller & Franchise Agent',
                'description' => 'Wholesale bandwidth buyers, sub-ISPs, and hotspot voucher agents.',
                'badge' => 'bg-purple-500/10 text-purple-500 border border-purple-500/20',
                'count' => Customer::where('customer_type', 'reseller')->count(),
                'active' => Customer::where('customer_type', 'reseller')->where('status', 'active')->count(),
            ],
        ];

        $query = Customer::with(['package', 'branch'])->latest();

        if ($selectedType !== 'all' && array_key_exists($selectedType, $groups)) {
            $query->where('customer_type', $selectedType);
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('company_name', 'ilike', "%{$search}%")
                  ->orWhere('account_number', 'ilike', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.groups.index', compact('groups', 'customers', 'selectedType'));
    }
}
