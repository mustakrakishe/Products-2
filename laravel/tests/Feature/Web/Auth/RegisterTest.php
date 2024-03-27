<?php

namespace Tests\Feature\Web\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('validRegisterDataProvider')]
    public function test_if_input_is_valid_then_creates_records_and_sends_email_and_returns_notice(array $input): void
    {
        Notification::fake();

        $response = $this->post(route('register'), $input);

        $user = User::firstWhere('email', trim($input['email']));

        $response->assertFound();
        $response->assertRedirectToRoute('verification.notice');
        $this->assertDatabaseRecordsAreCorrect($user, $input);
        $this->assertSendsCorrectMail($user);
    }

    protected function assertDatabaseRecordsAreCorrect(User $user, array $input): void
    {
        $this->assertNotNull($user->id);
        $this->assertEquals($user->name, trim($input['name']));
        $this->assertEquals($user->email, trim($input['email']));
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check($input['password'], $user->password));
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    protected function assertSendsCorrectMail(User $user): void
    {
        Notification::assertCount(1);
        Notification::assertSentTo(
            $user,
            VerifyEmail::class,
            function (VerifyEmail $notification, $channels, $notifiable) use ($user) {
                $mailData = $notification->toMail($notifiable)->toArray();

                return $this->isLinkValid($mailData['actionUrl'], $user);
            }
        );
    }

    protected function isLinkValid(string $link, User $user): bool
    {
        $request = Request::create($link);

        $expectedUrl = route('verification.verify', [
            'id'   => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        if ($request->url() !== $expectedUrl) {
            return false;
        }

        return $request->hasValidSignature();
    }

    public static function validRegisterDataProvider(): array
    {
        return [
            'regular' => [[
                'name'                  => 'New User',
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'name_is_trimless' => [[
                'name'                  => ' New User ',
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'name_is_shortest' => [[
                'name'                  => 'u',
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'name_is_longest' => [[
                'name'                  => str_repeat('a', 255),
                'email'                 => 'user@example.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'email_is_trimless' => [[
                'name'                  => 'New User',
                'email'                 => ' user@example.com ',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'email_is_shortest' => [[
                'name'                  => 'New User',
                'email'                 => 'a@a',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'email_is_longest' => [[
                'name'                  => 'New User',
                'email'                 => str_repeat('a', 253).'@a',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]],
            'password_is_trimless' => [[
                'name'                  => 'New User',
                'email'                 => 'user@example.com',
                'password'              => ' password ',
                'password_confirmation' => ' password ',
            ]],
            'password_is_shortest' => [[
                'name'                  => 'New User',
                'email'                 => 'user@example.com',
                'password'              => 'p',
                'password_confirmation' => 'p',
            ]],
            'password_is_longest' => [[
                'name'                  => 'New User',
                'email'                 => 'user@example.com',
                'password'              => str_repeat('a', 255),
                'password_confirmation' => str_repeat('a', 255),
            ]],
        ];
    }
}
