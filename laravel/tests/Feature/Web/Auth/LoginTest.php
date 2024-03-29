<?php

namespace Tests\Feature\Web\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_redirects_to_home(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->post(route('login'), [
                'email'    => 'user@example.com',
                'password' => 'password',
            ]);

        $response->assertRedirectToRoute('home');
    }

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_authenticates_and_redirects_to_home(array $input): void
    {
        $user = User::factory()->create([
            'email'    => trim($input['email']),
            'password' => $input['password'],
        ]);

        $response = $this->post(route('login'), $input);

        $response->assertFound();
        $response->assertRedirectToRoute('home');
        $this->assertAuthenticatedAs($user);
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

    public function test_if_has_intended_then_redirects_to_intended(): void
    {
        $credentials = [
            'email'    => 'user@example.com',
            'password' => 'password',
        ];

        $user = User::factory()->create($credentials);

        session(['url.intended' => route('products.index')]);

        $response = $this->post(route('login'), $credentials);

        $response->assertFound();
        $response->assertRedirectToRoute('products.index');
        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('invalidInputDataProvider')]
    public function test_if_input_is_invalid_then_fails_validation(array $errors, array $input): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(
            route('login'),
            $input,
            ['HTTP_REFERER' => route('login')]
        );

        $response->assertRedirectToRoute('login');
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

        $response = $this->post(
            route('login'),
            [
                'email'    => 'user@example.com',
                'password' => 'password',
            ],
            [
                'HTTP_REFERER' => route('login')
            ]
        );

        $response->assertFound();
        $response->assertRedirectToRoute('login');
        $response->assertInvalid(['email', 'password']);
    }
}
