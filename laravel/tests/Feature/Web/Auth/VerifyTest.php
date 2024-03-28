<?php

namespace Tests\Feature\Web\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_redirects_to_maim(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get($this->makeLink($user));

        $response->assertFound();
        $response->assertRedirectToRoute('home');
    }

    protected function makeLink(User $user): string
    {
        $name = 'verification.verify';
        $expiration = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
        $parameters = [
            'id'   => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ];

        return URL::temporarySignedRoute($name, $expiration, $parameters);
    }

    public function test_if_link_is_valid_then_makes_db_records_and_logins_and_redirect_to_home(): void
    {
        $user = User::factory()->create();

        $response = $this->get($this->makeLink($user));

        $user->refresh();

        $response->assertFound();
        $response->assertRedirectToRoute('home');
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('invalidVerifyDataProvider')]
    public function test_if_link_is_invalid_then_returns_forbidden(callable $createUrlUsingCallback): void
    {
        $user = User::factory()->create();
        $url = $createUrlUsingCallback(
            $user->getKey(),
            $user->getEmailForVerification()
        );

        $response = $this->get($url);

        $response->assertForbidden();
    }

    public static function invalidVerifyDataProvider(): array
    {
        return [
            'signature_is_wrong' => [
                'create_url_using_callback' => function (int $id, string $email) {
                    return route('verification.verify', [
                        'id'        => $id,
                        'hash'      => sha1($email),
                        'signature' => 'wrong_signature',
                        'expires'   => Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                    ]);
                },
            ],
            'hash_is_for_different_email' => [
                'create_url_using_callback' => function (int $id, string $email) {
                    $hash = sha1(User::factory()->create()->email);
                    return URL::temporarySignedRoute(
                        'verification.verify',
                        Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                        compact('id', 'hash')
                    );
                },
            ],
            'link_is_expired' => [
                'create_url_using_callback' => function (int $id, string $email) {
                    $hash = sha1($email);
                    return URL::temporarySignedRoute(
                        'verification.verify',
                        Carbon::now()->subMinutes(1),
                        compact('id', 'hash')
                    );
                },
            ],
        ];
    }
}
