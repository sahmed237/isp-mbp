<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NetworkDeviceRequest;
use App\Models\Branch;
use App\Models\NetworkDevice;
use App\Services\NetworkDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NetworkDeviceController extends Controller
{
    public function __construct(
        protected NetworkDeviceService $deviceService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', NetworkDevice::class);

        $filters = $request->only(['search', 'status', 'device_type', 'branch_id']);
        $devices = $this->deviceService->getPaginatedDevices($filters);
        $branches = Branch::where('status', 'active')->get();

        return view('admin.network-devices.index', compact('devices', 'branches', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', NetworkDevice::class);

        $branches = Branch::where('status', 'active')->get();

        return view('admin.network-devices.create', compact('branches'));
    }

    public function store(NetworkDeviceRequest $request): RedirectResponse
    {
        Gate::authorize('create', NetworkDevice::class);

        $device = $this->deviceService->createDevice($request->validated());

        return redirect()->route('network-devices.index')
            ->with('success', "Device '{$device->name}' registered successfully.");
    }

    public function show(NetworkDevice $networkDevice): View
    {
        Gate::authorize('view', $networkDevice);

        $networkDevice->load(['branch', 'organization']);

        return view('admin.network-devices.show', ['device' => $networkDevice]);
    }

    public function edit(NetworkDevice $networkDevice): View
    {
        Gate::authorize('update', $networkDevice);

        $branches = Branch::where('status', 'active')->get();

        return view('admin.network-devices.edit', ['device' => $networkDevice, 'branches' => $branches]);
    }

    public function update(NetworkDeviceRequest $request, NetworkDevice $networkDevice): RedirectResponse
    {
        Gate::authorize('update', $networkDevice);

        $this->deviceService->updateDevice($networkDevice, $request->validated());

        return redirect()->route('network-devices.index')
            ->with('success', "Device '{$networkDevice->name}' updated successfully.");
    }

    public function destroy(NetworkDevice $networkDevice): RedirectResponse
    {
        Gate::authorize('delete', $networkDevice);

        $this->deviceService->deleteDevice($networkDevice);

        return redirect()->route('network-devices.index')
            ->with('success', "Device '{$networkDevice->name}' deleted successfully.");
    }

    public function testConnection(NetworkDevice $networkDevice, Request $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('testConnection', $networkDevice);

        $result = $this->deviceService->testConnection($networkDevice);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }
}
