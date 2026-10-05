<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerLocationController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::where('status', 'active')->get();

        // Location summaries
        $citiesCount = Customer::whereNotNull('city')->distinct('city')->count('city');
        $withGpsCount = Customer::whereNotNull('gps_coordinates')->where('gps_coordinates', '!=', '')->count();
        $totalSites = Customer::whereNotNull('installation_address')->where('installation_address', '!=', '')->count();

        $query = Customer::with(['branch', 'package'])
            ->latest();

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('state')) {
            $query->where('state', $request->query('state'));
        }

        if ($request->filled('has_gps')) {
            if ($request->query('has_gps') === 'yes') {
                $query->whereNotNull('gps_coordinates')->where('gps_coordinates', '!=', '');
            } elseif ($request->query('has_gps') === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('gps_coordinates')->orWhere('gps_coordinates', '');
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('installation_address', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%")
                  ->orWhere('state', 'ilike', "%{$search}%")
                  ->orWhere('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('account_number', 'ilike', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        // Top cities breakdown
        $topCities = Customer::select('city', DB::raw('count(*) as total'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.customers.locations.index', compact(
            'customers',
            'branches',
            'citiesCount',
            'withGpsCount',
            'totalSites',
            'topCities'
        ));
    }
}
