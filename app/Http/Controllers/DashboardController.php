<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('dashboard.view');

        $metrics = $this->dashboardService->getMetrics();

        return view('admin.dashboard', $metrics);
    }
}
