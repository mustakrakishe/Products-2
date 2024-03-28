<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $response = $this->post(route('api.logout'));

        $response->assertUnauthorized();
    }

    public function test_if_authorized_then_removes_current_token_and_returns_correct_response(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('Current');
        $anotherToken = $user->createToken('Another');

        $response = $this
            ->withHeader(
                'Authorization',
                sprintf('Bearer %s', $currentToken->plainTextToken)
            )
            ->post(route('api.logout'));

        $response->assertNoContent();
        $this->assertCount(1, $user->tokens);
        $this->assertEquals(
            $anotherToken->accessToken->id,
            $user->tokens()->first()->id
        );
    }
}
