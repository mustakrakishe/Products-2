<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('api.password.reset'), [
                'token'                 => Password::createToken($user),
                'email'                 => $user->email,
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertForbidden();
    }

    #[DataProvider('validResetPasswordDataProvider')]
    public function test_if_data_is_valid_then_update_db_and_returns_ok(array $input): void
    {
        $user = User::factory()->create([
            'email'    => trim($input['email']),
            'password' => 'old_password',
        ]);

        $response = $this->post(route('api.password.reset'), $input + [
            'token' => Password::createToken($user),
        ]);

        $user->refresh();

        $response->assertOk();
        $this->assertDatabaseRecordsAreCorrect($user);
    }

    protected function assertDatabaseRecordsAreCorrect(User $user): void
    {
        $this->assertFalse(
            DB::table('password_reset_tokens')
                ->whereEmail($user->email)
                ->exists()
        );
        $this->assertFalse(
            Hash::check('old_password', $user->password)
        );
    }

    public static function validResetPasswordDataProvider(): array
    {
        return [
            'regular' => [[
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'email_is_trimless' => [[
                'email'                 => ' user@example.com ',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'email_is_shortest' => [[
                'email'                 => 'a@a',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'email_is_longest' => [[
                'email'                 => str_repeat('a', 253).'@a',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'password_is_trimless' => [[
                'email'                 => 'user@example.com',
                'password'              => ' password ',
                'password_confirmation' => ' password ',
            ]],
            'password_is_shortest' => [[
                'email'                 => 'user@example.com',
                'password'              => 'p',
                'password_confirmation' => 'p',
            ]],
            'password_is_longest' => [[
                'email'                 => 'user@example.com',
                'password'              => str_repeat('a', 255),
                'password_confirmation' => str_repeat('a', 255),
            ]],
        ];
    }

    #[DataProvider('invalidPasswordResetDataProvider')]
    public function test_if_input_is_invalid_then_fails_validation(string $invalid, callable $inputCallback): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->post(
            route('api.password.reset'),
            $inputCallback($user)
        );

        $response->assertUnprocessable();
        $response->assertInvalid($invalid);
    }

    public static function invalidPasswordResetDataProvider(): array
    {
        return [
            'token_is_missing' => [
                'invalid' => 'token',
                'inputCallback' => function (User $user) {
                    return [
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'token_is_null' => [
                'invalid' => 'token',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => null,
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'token_is_empty' => [
                'invalid' => 'token',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => '',
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'token_is_unexisted' => [
                'invalid' => 'token',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => 'unexisted_token',
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'token_is_wrong' => [
                'invalid' => 'token',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user).'1',
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_is_missing' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_is_null' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => null,
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_is_empty' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => '',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_has_wrong_format' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user.example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_has_wrong_data_type' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 123,
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_is_too_long' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => str_repeat('a', 254).'@a',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'email_is_unexisted' => [
                'invalid' => 'email',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'unexisted@.example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'password_is_missing' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password_confirmation' => 'password',
                    ];
                },
            ],
            'password_is_null' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => null,
                        'password_confirmation' => null,
                    ];
                },
            ],
            'password_is_empty' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => '',
                        'password_confirmation' => '',
                    ];
                },
            ],
            'password_has_wrong_data_type' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => 123,
                        'password_confirmation' => 123,
                    ];
                },
            ],
            'password_is_too_long' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => str_repeat('a', 256),
                        'password_confirmation' => str_repeat('a', 256),
                    ];
                },
            ],
            'password_confirmation_is_missing' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                    ];
                },
            ],
            'password_confirmation_is_null' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => null,
                    ];
                },
            ],
            'password_confirmation_is_different_form_password' => [
                'invalid' => 'password',
                'inputCallback' => function (User $user) {
                    return [
                        'token'                 => Password::createToken($user),
                        'email'                 => 'user@example.com',
                        'password'              => 'password',
                        'password_confirmation' => 'password_confirmation',
                    ];
                },
            ],
        ];
    }
}
