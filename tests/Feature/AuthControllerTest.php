<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Notifications\CustomPasswordResetNotification;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);
        $response = $this->post('api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token'],
                'message',
                'status',
            ])
            ->assertJsonPath('message', AuthController::SUCCESS_AUTHENTICATED)
            ->assertJsonPath('status', 200);
    }

    public function test_login_permitted_when_email_not_verified(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'email_verified_at' => null,
        ]);
        $response = $this->post('api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(403)
            ->assertJsonStructure($this->getErrorStructure());
    }

    public function test_register(): void
    {
        Notification::fake();

        $response = $this->post('api/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', AuthController::SUCCESS_REGISTERED)
            ->assertJsonStructure(['message', 'status']);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);

        Notification::assertSentTo(
            User::firstWhere('email', 'john.doe@example.com'),
            CustomVerifyEmailNotification::class
        );
    }

    public function test_register_with_existing_user(): void
    {
        User::factory()->create(['email' => 'john.doe@example.com']);

        $response = $this->post('api/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', 'The email has already been taken.')
            ->assertJsonPath('errors.0.source', 'email');
    }

    public function test_verify_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = $this->generateVerificationUrl($user);

        $response = $this->get($verificationUrl);

        $response->assertStatus(200)
            ->assertJsonPath('message', AuthController::SUCCESS_EMAIL_VERIFIED);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    // test_verify_email timed out
    public function test_verify_email_with_invalid_url_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = $this->generateVerificationUrl($user, invalidHash: true);

        $response = $this->get($verificationUrl);
        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', AuthController::ERROR_INVALID_VERIFICATION_LINK)
            ->assertJsonPath('errors.0.source', null);
    }

    public function test_verify_email_with_expired_url_signature(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $expiredVerificationUrl = $this->generateVerificationUrl($user, false, true);

        $response = $this->get($expiredVerificationUrl);

        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', 'Invalid signature.')
            ->assertJsonPath('errors.0.source', null);

        // Assert the user's email is still not verified
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_verification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->post('api/email/resend-verification', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', AuthController::SUCCESS_EMAIL_VERIFICATION_SENT);

        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    }

    public function test_resend_verification_for_verified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('api/email/resend-verification', [
            'email' => $user->email,
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', AuthController::ERROR_EMAIL_ALREADY_VERIFIED)
            ->assertJsonPath('errors.0.source', null);
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->post('api/password/forgot', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', AuthController::SUCCESS_RESET_LINK_SENT);

        Notification::assertSentTo($user, CustomPasswordResetNotification::class);
    }

    public function test_forgot_password_with_nonexistent_email(): void
    {
        $response = $this->post('api/password/forgot', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', AuthController::SUCCESS_RESET_LINK_SENT);
    }

    public function test_validate_reset_token_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);
        $response = $this->getJson("api/password/validate-reset-token/{$token}?email={$user->email}");
        $response->assertStatus(200)
            ->assertJsonPath('message', AuthController::SUCCESS_VALID_RESET_TOKEN)
            ->assertJsonPath('data.reset_token', $token);
    }

    public function test_validate_reset_token_with_invalid_token(): void
    {
        $user = User::factory()->create();
        $response = $this->getJson("api/password/validate-reset-token/invalid-token?email={$user->email}");
        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', AuthController::ERROR_INVALID_RESET_TOKEN)
            ->assertJsonPath('errors.0.source', null);
    }

    public function test_validate_reset_token_with_nonexistent_email(): void
    {
        $user = User::factory()->create();
        $validToken = Password::createToken($user);
        $response = $this->getJson("api/password/validate-reset-token/{$validToken}?email=nonexistent@example.com");
        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', AuthController::ERROR_INVALID_RESET_TOKEN)
            ->assertJsonPath('errors.0.source', null);
    }

    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', AuthController::SUCCESS_PASSWORD_RESET);

        $this->assertTrue(Hash::check('new_password', $user->fresh()->password));
    }

    public function test_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post('api/password/reset', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', 'This password reset token is invalid.')
            ->assertJsonPath('errors.0.source', null);
    }

    public function test_reset_password_with_non_matching_password_confirmation(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new_password',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure($this->getErrorStructure())
            ->assertJsonPath('errors.0.message', 'The password confirmation does not match.')
            ->assertJsonPath('errors.0.source', 'password');
    }

    public function test_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $tokenData = [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'TestToken',
        ];
        $this->assertDatabaseHas('personal_access_tokens', $tokenData);
        $response = $this->post('api/logout', [], ['Authorization' => 'Bearer ' . $token]);
        $this->assertDatabaseMissing('personal_access_tokens', $tokenData);
        $response->assertStatus(200);
    }

    public function test_logout_when_unauthenticated(): void
    {
        $response = $this->post('api/logout', [], ['Authorization' => 'Bearer invalid_token']);
        $response->assertStatus(401)
            ->assertJsonStructure($this->getErrorStructure());
    }

    private function generateVerificationUrl($user, $invalidHash = false, $expired = false): string
    {
        $expires = $expired ? now()->subMinutes(1) : now()->addMinutes(60);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            $expires,
            [
                'uuid' => $user->uuid,
                'hash' => $invalidHash ? 'invalid_hash' : sha1($user->email),
            ]
        );

        return str_replace(config('app.url'), 'http://localhost:3001', $verificationUrl);
    }
}
