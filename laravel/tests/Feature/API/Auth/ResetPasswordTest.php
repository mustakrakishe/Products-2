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
            ->post('api/auth/password/reset', [
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

        $response = $this->post('api/auth/password/reset', $input + [
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
}
