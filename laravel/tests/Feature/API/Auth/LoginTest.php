<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->post('api/auth/login');

        $response->assertForbidden();
    }

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_returns_actual_data(array $input): void
    {
        $user = User::factory()->create([
            'email'    => trim($input['email']),
            'password' => $input['password'],
        ]);

        $response = $this->post('api/auth/login', $input);

        $token = $user->tokens()->first();

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id'           => $token->id,
                'last_used_at' => $token->last_used_at?->format('Y-m-d H:i:s'),
                'created_at'   => $token->created_at?->format('Y-m-d H:i:s'),
                'updated_at'   => $token->updated_at?->format('Y-m-d H:i:s'),
                'tokenable'    => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                    'created_at'        => $user->created_at?->format('Y-m-d H:i:s'),
                    'updated_at'        => $user->updated_at?->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
        $this->assertEquals(
            $token->id,
            PersonalAccessToken::findToken($response->decodeResponseJson()['data']['token'])->id
        );
    }

    public static function validInputDataProvider(): array
    {
        return [
            'regular' => [
                [
                    'email'    => 'user@example.com',
                    'password' => 'password',
                ],
            ],
            'email_is_trimless' => [
                [
                    'email'    => ' user@example.com ',
                    'password' => 'password',
                ],
            ],
            'email_is_shortest' => [
                [
                    'email'    => 'a@a.a',
                    'password' => 'password',
                ],
            ],
            'email_is_longest' => [
                [
                    'email'    => str_repeat('a', 253).'@a',
                    'password' => 'password',
                ],
            ],
            'password_is_trimless' => [
                [
                    'email'    => 'user@example.com',
                    'password' => ' password ',
                ],
            ],
            'password_is_shortest' => [
                [
                    'email'    => 'user@example.com',
                    'password' => 'p',
                ],
            ],
            'email_is_longest' => [
                [
                    'email'    => 'user@example.com',
                    'password' => str_repeat('a', 255),
                ],
            ],
        ];
    }
}
