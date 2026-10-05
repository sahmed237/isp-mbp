<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SettingController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Access denied. System settings are restricted exclusively to Super Administrators.');
    }

    public function index(Request $request): View
    {
        $this->ensureSuperAdmin();

        $group = $request->query('group', 'general');
        $validGroups = ['general', 'email', 'payments', 'notifications'];

        if (!in_array($group, $validGroups, true)) {
            $group = 'general';
        }

        // Self-heal and ensure payment settings are in payments group and correctly typed
        if ($group === 'payments') {
            Setting::where(function ($q) {
                $q->where('key', 'like', 'paystack_%')
                  ->orWhere('key', 'like', 'monnify_%')
                  ->orWhere('key', 'like', 'zainpay_%');
            })->where('group', '!=', 'payments')->update(['group' => 'payments']);

            Setting::where('key', 'like', '%_active')->where('type', '!=', 'boolean')->update(['type' => 'boolean']);
            Setting::where('key', 'like', '%_mode_live')->where('type', '!=', 'boolean')->update(['type' => 'boolean']);
        }

        $settings = Setting::where('group', $group)->get();

        return view('admin.settings.index', compact('settings', 'group'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $group = $request->input('group', 'general');
        $inputs = $request->except(['_token', '_method', 'group']);

        $beforeState = Setting::where('group', $group)->pluck('value', 'key')->toArray();

        foreach ($inputs as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                if ($setting->type === 'boolean' || str_ends_with($key, '_active') || str_ends_with($key, '_live')) {
                    $value = ($value === 'on' || $value === '1' || $value === true) ? '1' : '0';
                }
                $setting->update(['value' => $value]);
            }
        }

        // Handle missing boolean switches (unchecked checkboxes)
        $booleanSettings = Setting::where('group', $group)
            ->where(function ($q) {
                $q->where('type', 'boolean')
                  ->orWhere('key', 'like', '%_active')
                  ->orWhere('key', 'like', '%_live');
            })
            ->get();
        foreach ($booleanSettings as $setting) {
            if (!isset($inputs[$setting->key])) {
                if ($group === 'payments') {
                    // Only deactivate if settings for this specific gateway were part of the submission
                    $gateway = explode('_', $setting->key)[0];
                    $wasGatewaySubmitted = false;
                    foreach (array_keys($inputs) as $inputKey) {
                        if (str_starts_with($inputKey, $gateway . '_')) {
                            $wasGatewaySubmitted = true;
                            break;
                        }
                    }
                    if ($wasGatewaySubmitted) {
                        $setting->update(['value' => '0']);
                    }
                } else {
                    $setting->update(['value' => '0']);
                }
            }
        }

        $afterState = Setting::where('group', $group)->pluck('value', 'key')->toArray();

        // Record Audit Log entry
        AuditLog::record(
            action: 'updated',
            description: "Updated '{$group}' platform settings",
            model: null,
            oldValues: $beforeState,
            newValues: $afterState,
            organizationId: Auth::user()?->organization_id
        );

        return redirect()->route('settings.index', ['group' => $group])
            ->with('success', ucfirst($group) . ' settings updated successfully.');
    }

    public function sendTestMail(Request $request): JsonResponse
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Setting::configureMailer();

            Mail::to($request->email)->send(new TestMail());

            return response()->json([
                'success' => true,
                'message' => "Test email successfully dispatched to {$request->email} using current SMTP settings!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
