<?php

namespace Tests\Feature\Web\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_guest_then_redirects_to_home(): void
    {
        $response = $this->get(route('logout'));

        $response->assertFound();
        $response->assertRedirectToRoute('login');
    }

    public function test_if_authorized_then_removes_authorization_and_returns_correct_response(): void
    {
        $user = User::factory()->create();

        $this->be($user);

        $this->assertAuthenticatedAs($user);

        $response = $this
            ->actingAs($user)
            ->get(route('logout'));

        $this->assertGuest();
        $response->assertRedirectToRoute('login');
    }
}
