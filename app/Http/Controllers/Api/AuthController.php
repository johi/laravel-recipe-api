<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Requests\Api\RegisterUserRequest;
use App\Http\Requests\Api\ResendEmailVerificationRequest;
use App\Models\User;
use App\Permissions\V1\Abilities;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponses;

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
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

        if (!$user->hasVerifiedEmail()) {
            return $this->error('Email address not verified.', 403);
        }

        return $this->ok(
            'Authenticated',
            [
                'token' => self::createToken($user),
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
        return $this->success('Registration successful, please verify your email.', [], 201);
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
            return $this->error('Invalid verification link.', 400);
        }
        $user->markEmailAsVerified();
        return $this->success('Email verified successfully.', [], 200);
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
            return $this->error('Email is already verified.', 400);
        }

        $user->sendEmailVerificationNotification();
        return $this->ok('Verification email resent.');
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
