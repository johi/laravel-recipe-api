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
     * @hideFromAPIDocumentation
     * @unauthenticated
     * @group Authentication
     * @response {"data":{},"message":"Registered","status":200}
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

    public function verify(Request $request, $uuid, $hash)
    {
        // Find the user by ID
        $user = User::where('uuid', $request->route('uuid'))->firstOrFail();

        // Check if the hash matches the user's email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if ($request->hasValidSignature()) {
            $user->markEmailAsVerified();
            return response()->json(['message' => 'Email verified successfully.']);
        }

        return $this->success('Email verified successfully.', [], 200);
    }

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
