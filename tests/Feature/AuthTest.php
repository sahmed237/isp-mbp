<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:superadmin@isp-mbp.local|127.0.0.1');
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ISP-MBP Portal');
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::where('email', 'superadmin@isp-mbp.local')->first();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'superadmin@isp-mbp.local',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'superadmin@isp-mbp.local',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::where('email', 'inactive_staff_test@isp-mbp.local')->forceDelete();

        $inactiveUser = User::create([
            'name' => 'Suspended Staff',
            'email' => 'inactive_staff_test@isp-mbp.local',
            'password' => Hash::make('Password123!'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive_staff_test@isp-mbp.local',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $inactiveUser->delete();
    }

    public function test_user_can_logout_securely(): void
    {
        $user = User::where('email', 'superadmin@isp-mbp.local')->first();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_view_profile_and_update_information(): void
    {
        $user = User::where('email', 'readonly@isp-mbp.local')->first();

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee($user->name);

        $updateResponse = $this->actingAs($user)->put('/profile', [
            'name' => 'Khadijah Usman Updated',
            'phone' => '+234 803 999 8888',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Khadijah Usman Updated',
            'phone' => '+234 803 999 8888',
        ]);

        // Restore original name
        $user->update(['name' => 'Khadijah Usman', 'phone' => '+234 803 000 0008']);
    }
}
