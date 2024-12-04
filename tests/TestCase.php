<?php

namespace Tests;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function makeAuthenticatedRequestWithToken(User $user, string $method, string $route, array $data = []): \Illuminate\Testing\TestResponse
    {
        // Generate a token for the user
        $token = AuthController::createToken($user);

        if ($method === 'DELETE') {
            return $this->deleteJson($route, $data, [
                'Authorization' => "Bearer $token"
            ]);
        }
        // Make the authenticated request
        return $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->json($method, $route, $data);
    }

    protected function getAuthenticatedUserJsonGet(User $user, string $route, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->makeAuthenticatedRequestWithToken($user, 'GET', $route, $data);
    }
    protected function getAuthenticatedJsonPost(User $user, string $route, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->makeAuthenticatedRequestWithToken($user, 'POST', $route, $data);
    }

    protected function getAuthenticatedJsonPut(User $user, string $route, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->makeAuthenticatedRequestWithToken($user, 'PUT', $route, $data);
    }

    protected function getAuthenticatedJsonPatch(User $user, string $route, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->makeAuthenticatedRequestWithToken($user, 'PATCH', $route, $data);
    }

    protected function getAuthenticatedJsonDelete(User $user, string $route, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->makeAuthenticatedRequestWithToken($user, 'DELETE', $route, $data);
    }
}
