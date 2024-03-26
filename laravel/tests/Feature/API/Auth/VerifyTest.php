<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get($this->makeLink($user));

        $response->assertForbidden();
    }

    protected function makeLink(User $user): string
    {
        $name = 'api.verification.verify';
        $expiration = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
        $parameters = [
            'id'   => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ];

        return URL::temporarySignedRoute($name, $expiration, $parameters);
    }
}
