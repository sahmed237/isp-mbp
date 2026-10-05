<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NasRequest;
use App\Models\Radius\Nas;
use App\Services\RadiusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadiusController extends Controller
{
    public function __construct(
        protected RadiusService $radiusService
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'sessions');
        $validTabs = ['sessions', 'nas', 'accounting', 'auth-logs'];
        if (!in_array($tab, $validTabs, true)) {
            $tab = 'sessions';
        }

        $stats = $this->radiusService->getStats();

        $activeSessions = null;
        $nasClients = null;
        $historicalSessions = null;
        $authLogs = null;

        if ($tab === 'sessions') {
            $activeSessions = $this->radiusService->getActiveSessions($request->only('search'));
        } elseif ($tab === 'nas') {
            $nasClients = $this->radiusService->getNasClients();
        } elseif ($tab === 'accounting') {
            $historicalSessions = $this->radiusService->getHistoricalSessions($request->only(['search', 'terminate_cause']));
        } elseif ($tab === 'auth-logs') {
            $authLogs = $this->radiusService->getAuthLogs($request->only(['search', 'status']));
        }

        return view('admin.radius.index', compact(
            'tab',
            'stats',
            'activeSessions',
            'nasClients',
            'historicalSessions',
            'authLogs'
        ));
    }

    public function storeNas(NasRequest $request): RedirectResponse
    {
        $nas = $this->radiusService->createNas($request->validated());

        return redirect()->route('radius.index', ['tab' => 'nas'])
            ->with('success', "NAS Router {$nas->shortname} ({$nas->nasname}) registered successfully.");
    }

    public function updateNas(NasRequest $request, Nas $nas): RedirectResponse
    {
        $this->radiusService->updateNas($nas, $request->validated());

        return redirect()->route('radius.index', ['tab' => 'nas'])
            ->with('success', "NAS Router {$nas->shortname} updated successfully.");
    }

    public function destroyNas(Nas $nas): RedirectResponse
    {
        $this->radiusService->deleteNas($nas);

        return redirect()->route('radius.index', ['tab' => 'nas'])
            ->with('success', "NAS Router {$nas->shortname} removed successfully.");
    }
}
