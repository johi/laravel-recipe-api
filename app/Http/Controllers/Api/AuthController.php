<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Requests\Api\RegisterUserRequest;
use App\Http\Requests\Api\ResendEmailVerificationRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Permissions\V1\Abilities;
use App\Models\User;

class AuthController extends Controller
{
    use ApiResponses;

    ### SUCCESS MESSAGES
    const SUCCESS_AUTHENTICATED = 'Authenticated';
    const SUCCESS_REGISTERED = 'Registration successful, please verify your email';
    const SUCCESS_EMAIL_VERIFIED = 'Email verified successfully';
    const SUCCESS_EMAIL_VERIFICATION_SENT = 'Email verification sent successfully';
    const SUCCESS_RESET_LINK_SENT = 'If the email exists in our database, you will receive an email with instructions on how to reset your password. Please check your inbox, including the spam/junk folder.';
    const SUCCESS_VALID_RESET_TOKEN = 'Valid token';
    const SUCCESS_PASSWORD_RESET = 'Password reset successfully';

    ### ERROR MESSAGES
    const ERROR_INVALID_CREDENTIALS = 'Invalid credentials';
    const ERROR_EMAIL_NOT_VERIFIED = 'Email address not verified';
    const ERROR_INVALID_VERIFICATION_LINK = 'Invalid verification link';
    const ERROR_EMAIL_ALREADY_VERIFIED = 'Email already verified';
    const ERROR_INVALID_RESET_TOKEN = 'Invalid or expired reset token';

    /**
     * Login
     *
     * Authenticates the user and returns the user's API token
     *
     * @unauthenticated
     * @group Authentication
     * @response {"data":{"token":"{YOUR_AUTH_KEY}"},"message":"Authenticated","status":200}
     */
    public function login(LoginUserRequest $request) {
        $request->validated($request->all());
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error(self::ERROR_INVALID_CREDENTIALS, 401);
        }

        $user = User::firstWhere('email', $request->email);

        // This should be ok, since we only reach this part when the user provided correct credentials.
        if (!$user->hasVerifiedEmail()) {
            return $this->error(self::ERROR_EMAIL_NOT_VERIFIED, 403);
        }

        return $this->ok(
            self::SUCCESS_AUTHENTICATED,
            [
                'token' => self::createToken($user),
                'user' => new UserResource($user),
            ]
        );
    }

    /**
     * Register
     *
     * @unauthenticated
     * @group Authentication
     * @response {"data":{},"message":"Registration successful, please verify your email.","status":201}
     */
    public function register(RegisterUserRequest $request) {
        $request->validated($request->all());
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->sendEmailVerificationNotification();
        return $this->success(self::SUCCESS_REGISTERED, [], 201);
    }

    /**
     * Verify email
     *
     * @unauthenticated
     * @group Authentication
     */
    public function verify(Request $request, $uuid, $hash)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        if ($hash !== sha1($user->getEmailForVerification())) {
            return $this->error(self::ERROR_INVALID_VERIFICATION_LINK, 400);
        }
        $user->markEmailAsVerified();
        return $this->success(self::SUCCESS_EMAIL_VERIFIED, [], 200);
    }

    /**
     * Resend Email Verification Notification
     *
     * @unauthenticated
     * @group Authentication
     */
    public function resendVerification(ResendEmailVerificationRequest $request)
    {
        $request->validated($request->all());
        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return $this->error(self::ERROR_EMAIL_ALREADY_VERIFIED, 400);
        }

        $user->sendEmailVerificationNotification();
        return $this->ok(self::SUCCESS_EMAIL_VERIFICATION_SENT);
    }

    /**
     * Forgot Password
     *
     * @unauthenticated
     * @group Authentication
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            Password::sendResetLink($request->only('email'));
        }

        return $this->success(self::SUCCESS_RESET_LINK_SENT, [], 200);
    }

    /**
     * Validate Password Reset Token
     *
     * @unauthenticated
     * @group Authentication
     */
    public function validateResetToken(Request $request, $token)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);
        $credentials = [
            'email' => $request->input('email'),
            'token' => $token,
        ];
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Password::getRepository()->exists($user, $token)) {
            return $this->error(self::ERROR_INVALID_RESET_TOKEN, 400);
        }
        return $this->success(self::SUCCESS_VALID_RESET_TOKEN, [
            'reset_token' => $token,
        ]);
    }

    /**
     * Reset Password
     *
     * @unauthenticated
     * @group Authentication
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
                $user->tokens()->delete();
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            return $this->success(self::SUCCESS_PASSWORD_RESET, [], 200);
        }
        return $this->error(trans($status), 400);
    }

    /**
     * Logout
     *
     * Logs out the user and invalidates token
     *
     * @group Authentication
     * @response {"data":[],"message":"","status":200}
     */
    public function logout(Request $request) {
        Auth::user()->currentAccessToken()->delete();
        return $this->ok('');
    }

    public static function createToken(User $user) {
        return $user
            ->createToken(
                'API Token for ' . $user->email,
                Abilities::getAbilities($user),
                now()->addMonth()
            )
            ->plainTextToken;
    }

}
