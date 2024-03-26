<?php

namespace Tests\Feature\API\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_guest_then_returns_unauthorized(): void
    {
        $response = $this->post('api/auth/logout');

        $response->assertUnauthorized();
    }
}
