<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\NetworkDevice;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getMetrics(): array
    {
        $user = Auth::user();

        // Customer Stats
        $customerQuery = Customer::query();
        $totalCustomers = (clone $customerQuery)->count();
        $activeCustomers = (clone $customerQuery)->where('status', 'active')->count();
        $suspendedCustomers = (clone $customerQuery)->where('status', 'suspended')->count();
        $expiredCustomers = (clone $customerQuery)->where('status', 'expired')->count();
        $newCustomers = (clone $customerQuery)->where('created_at', '>=', now()->subDays(30))->count();

        // Network Device Stats
        $deviceQuery = NetworkDevice::query();
        $totalDevices = (clone $deviceQuery)->count();
        $onlineDevices = (clone $deviceQuery)->where('status', 'online')->count();
        $offlineDevices = (clone $deviceQuery)->where('status', 'offline')->count();
        $warningDevices = (clone $deviceQuery)->where('status', 'warning')->count();

        // Internet Package Stats
        $packagesCount = Package::count();

        // Subscriptions & Revenue (Mock/Aggregates for Phase 1 as specified in prompt)
        $subscriptionStats = [
            'active' => $activeCustomers,
            'expiring_soon' => 2,
            'expired' => $expiredCustomers,
            'suspended' => $suspendedCustomers,
        ];

        $revenueStats = [
            'today' => 38500.00,
            'this_month' => 482500.00,
            'outstanding' => 22000.00,
            'paid_invoices_count' => 18,
            'currency_symbol' => '₦',
        ];

        $networkStats = [
            'online_devices' => $onlineDevices,
            'offline_devices' => $offlineDevices,
            'warning_devices' => $warningDevices,
            'total_devices' => $totalDevices,
            'active_sessions' => 142, // Simulated active PPPoE/Hotspot sessions
            'network_alerts' => $warningDevices > 0 ? "1 warning (Rain fade on 60GHz link)" : "All links optimal",
        ];

        // Recent Activity from Audit Logs
        $recentActivities = AuditLog::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return [
            'customer_stats' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
                'suspended' => $suspendedCustomers,
                'expired' => $expiredCustomers,
                'new' => $newCustomers,
            ],
            'subscription_stats' => $subscriptionStats,
            'revenue_stats' => $revenueStats,
            'network_stats' => $networkStats,
            'packages_count' => $packagesCount,
            'recent_activities' => $recentActivities,
        ];
    }
}
