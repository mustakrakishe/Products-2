<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SendPasswordResetLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('api/auth/password/reset/send', [
                'email' => $user->email,
            ]);

        $response->assertForbidden();
    }

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_creates_token_and_sends_email_and_returns_ok(array $input): void
    {
        Notification::fake();

        $user = User::factory()->create(array_map('trim', $input));

        $response = $this->post('api/auth/password/reset/send', $input);

        $response->assertOk();
        $this->assertCreatesToken($user);
        $this->assertSendsCorrectMail($user);
    }

    protected function assertCreatesToken(User $user): void
    {
        $tokens = DB::table('password_reset_tokens')
            ->whereEmail($user->email)
            ->get();

        $this->assertCount(1, $tokens);
        $this->assertNotNull($tokens->first()->token);
    }

    protected function assertSendsCorrectMail(User $user): void
    {
        Notification::assertCount(1);
        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification, $channels, $notifiable) use ($user) {
                $mailData = $notification->toMail($notifiable)->toArray();

                return $this->isLinkValid($mailData['actionUrl'], $user);
            }
        );
    }

    protected function isLinkValid(string $link, User $user): bool
    {
        $request = Request::create($link);

        $expectedClientUrl = config(
            sprintf('clients.%s.urls.password_reset', request()->host())
        );

        if ($request->url() !== $expectedClientUrl) {
            return false;
        }

        return Password::tokenExists($user, $request->token);
    }

    public static function validInputDataProvider(): array
    {
        return [
            'regular' => [
                [
                    'email' => 'user@example.com',
                ],
            ],
            'email_is_trimless' => [
                [
                    'email' => ' user@example.com ',
                ],
            ],
            'email_is_shortest' => [
                [
                    'email' => 'u@e',
                ],
            ],
            'email_is_longest' => [
                [
                    'email' => str_repeat('a', 253).'@e',
                ],
            ],
        ];
    }

    #[DataProvider('invalidInputDataProvider')]
    public function test_if_input_is_invalid_then_fails_validation(array $input): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->post('api/auth/password/reset/send', $input);

        $response->assertUnprocessable();
        $response->assertInvalid('email');
    }

    public static function invalidInputDataProvider(): array
    {
        return [
            'email_is_missing' => [
                [],
            ],
            'email_is_null' => [
                [
                    'email' => null,
                ],
            ],
            'email_is_empty' => [
                [
                    'email' => '',
                ],
            ],
            'email_has_wrong_format' => [
                [
                    'email' => 'user.example.com',
                ],
            ],
            'email_has_wrong_data_type' => [
                [
                    'email' => 111,
                ],
            ],
            'email_is_too_long' => [
                [
                    'email' => str_repeat('a', 254).'@a',
                ],
            ],
            'email_does_not_exist' => [
                [
                    'email' => 'unexisted@example.com',
                ],
            ],
        ];
    }

    public function test_if_host_is_untrusted_then_returns_forbidden(): void
    {
        config(['clients' => []]);

        $response = $this->post('api/auth/password/reset/send', [
            'email' => User::factory()->create()->email,
        ]);

        $response->assertForbidden();
    }
}
