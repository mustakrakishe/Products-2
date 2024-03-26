<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('api/auth/password/reset', [
                'token'                 => Password::createToken($user),
                'email'                 => $user->email,
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertForbidden();
    }
}
