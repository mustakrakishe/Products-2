<?php

namespace Tests\Feature\API\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_authorized_then_returns_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get($this->makeLink($user));

        $response->assertForbidden();
    }

    protected function makeLink(User $user): string
    {
        $name = 'api.verification.verify';
        $expiration = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
        $parameters = [
            'id'   => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ];

        return URL::temporarySignedRoute($name, $expiration, $parameters);
    }

    public function test_if_valid_data_then_logins(): void
    {
        $user = User::factory()->create();

        $response = $this->get($this->makeLink($user));

        $token = $user->tokens()->first();
        $user->refresh();

        $response->assertOk();
        $this->assertDatabaseRecordsAreCorrect($user, $token);
        $this->assertResourceReturnsActualData($response, $user, $token);
    }

    protected function assertDatabaseRecordsAreCorrect(User $user, PersonalAccessToken $token): void
    {
        $this->assertNotNull($user->email_verified_at);

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

    #[DataProvider('invalidVerifyDataProvider')]
    public function test_if_input_is_invalid_then_returns_forbidden(callable $createUrlUsingCallback): void
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
                    return route('api.verification.verify', [
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
                        'api.verification.verify',
                        Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                        compact('id', 'hash')
                    );
                },
            ],
            'link_is_expired' => [
                'create_url_using_callback' => function (int $id, string $email) {
                    $hash = sha1($email);
                    return URL::temporarySignedRoute(
                        'api.verification.verify',
                        Carbon::now()->subMinutes(1),
                        compact('id', 'hash')
                    );
                },
            ],
        ];
    }
}
