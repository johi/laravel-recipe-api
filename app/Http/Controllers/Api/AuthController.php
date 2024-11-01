<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Permissions\V1\Abilities;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @response 200
     {
      "data": {
      "token": "{YOUR_AUTH_KEY}"
      },
      "message": "Authenticated",
      "status": 200
        }
     */
    public function login(LoginUserRequest $request) {
        $request->validated($request->all());

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

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
     * @response 200
    {
    "data": {
    },
    "message": "Registered",
    "status": 200
    }
     */
    public function register() {
        return $this->ok('Registered');
    }

    /**
     * Logout
     *
     * Logs out the user and invalidates token
     *
     * @group Authentication
     * @response 200
     {
      "data": [],
      "message": "",
      "status": 200
      }
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
