<?php

namespace Tests\Feature\Web\Auth;

use App\Models\User;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SendPasswordResetLinkTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('validInputDataProvider')]
    public function test_if_input_is_valid_then_creates_token_and_sends_email_and_returns_back(array $input): void
    {
        Notification::fake();
        ResetPassword::createUrlUsing(null);

        $user = User::factory()->create(array_map('trim', $input));

        $response = $this->post(
            route('password.reset.send'),
            $input,
            ['HTTP_REFERER' => route('password.reset.send')]
        );

        $response->assertFound();
        $response->assertRedirectToRoute('password.reset.send');
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

        try {
            $route = Route::getRoutes()->match($request);
        } catch (Exception $e) {
            return false;
        }

        return $route->named('password.reset')
            && Password::tokenExists($user, $route->parameter('token'));
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
        $response = $this->post(
            route('password.reset.send'),
            $input,
            ['HTTP_REFERER' => route('password.reset.send')]
        );

        $response->assertFound();
        $response->assertRedirectToRoute('password.reset.send');
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

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('password.reset.send'), [
                'email' => $user->email,
            ]);

        $response->assertFound();
        $response->assertRedirectToRoute('home');
    }
}
