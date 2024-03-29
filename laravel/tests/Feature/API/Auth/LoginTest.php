<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->post(route('api.login'), [
                'email'    => 'user@example.com',
                'password' => 'password',
            ]);

        $response->assertForbidden();
    }

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_creates_correct_records_and_returns_actual_data(array $input): void
    {
        $user = User::factory()->create([
            'email'    => trim($input['email']),
            'password' => $input['password'],
        ]);

        $response = $this->post(route('api.login'), $input);

        $token = $user->tokens()->first();

        $response->assertOk();
        $this->assertDatabaseRecordsAreCorrect($user, $token);
        $this->assertResourceReturnsActualData($response, $user, $token);

    }

    protected function assertDatabaseRecordsAreCorrect(User $user, PersonalAccessToken $token): void
    {
        $this->assertNotNull($token->id);
        $this->assertNull($token->last_used_at);
        $this->assertNotNull($token->created_at);
        $this->assertNotNull($token->updated_at);
        $this->assertEquals($token->tokenable_id, $user->id);
        $this->assertCount(1, $user->tokens);
    }

    protected function assertResourceReturnsActualData(TestResponse $response, User $user, PersonalAccessToken $token): void
    {
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
                    'email'    => 'a@a',
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

    #[DataProvider('invalidInputDataProvider')]
    public function test_if_input_is_invalid_then_fails_validation(array $errors, array $input): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('api.login'), $input);

        $response->assertUnprocessable();
        $response->assertInvalid($errors);
    }

    public static function invalidInputDataProvider(): array
    {
        return [
            'email_is_missing' => [
                'errors' => ['email'],
                'input'   => [
                    'password' => 'password',
                ],
            ],
            'email_is_null' => [
                'errors' => ['email'],
                'input'   => [
                    'email'    => null,
                    'password' => 'password',
                ],
            ],
            'email_is_empty' => [
                'errors' => ['email'],
                'input'   => [
                    'email'    => '',
                    'password' => 'password',
                ],
            ],
            'email_has_wrong_format' => [
                'errors' => ['email'],
                'input'   => [
                    'email'    => 'user.example.com',
                    'password' => 'password',
                ],
            ],
            'email_has_wrong_data_type' => [
                'errors' => ['email'],
                'input'   => [
                    'email'    => 123,
                    'password' => 'password',
                ],
            ],
            'email_is_too_long' => [
                'errors' => ['email'],
                'input'   => [
                    'email'    => str_repeat('a', 254).'@e',
                    'password' => 'password',
                ],
            ],
            'email_is_wrong' => [
                'errors' => ['email', 'password'],
                'input'   => [
                    'email'    => 'wrong@example.cpm',
                    'password' => 'password',
                ],
            ],
            'password_is_missing' => [
                'errors' => ['password'],
                'input'   => [
                    'email'    => 'user@example.com',
                ],
            ],
            'password_is_null' => [
                'errors' => ['password'],
                'input'   => [
                    'email'    => 'user@example.com',
                    'password' => null,
                ],
            ],
            'password_is_empty' => [
                'errors' => ['password'],
                'input'   => [
                    'email'    => 'user@example.com',
                    'password' => '',
                ],
            ],
            'password_has_wrong_data_type' => [
                'errors' => ['password'],
                'input'   => [
                    'email'    => 'user@example.com',
                    'password' => 123,
                ],
            ],
            'password_is_too_long' => [
                'errors' => ['password'],
                'input'   => [
                    'email'    => 'user@example.com',
                    'password' => str_repeat('a', 256),
                ],
            ],
            'password_is_wrong' => [
                'errors' => ['password', 'email'],
                'input'   => [
                    'email'    => 'user@example.com',
                    'password' => 'wrong_password',
                ],
            ],
        ];
    }

    public function test_if_email_is_not_verified_then_fails_validation(): void
    {
        User::factory()->create([
            'email'             => 'user@example.com',
            'password'          => 'password',
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('api.login'), [
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
        $response->assertInvalid(['email', 'password']);
    }
}
