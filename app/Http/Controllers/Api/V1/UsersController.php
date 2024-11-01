<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\AuthorFilter;
use App\Http\Requests\Api\V1\ReplaceUserRequest;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Policies\V1\UserPolicy;
use Illuminate\Support\Facades\Gate;

class UsersController extends ApiController
{
    protected string $policyClass = UserPolicy::class;
    private $possibleIncludes = ['recipes'];

    /**
     * Get all users
     *
     * @group User management
     */
    public function index(AuthorFilter $filters)
    {
        return UserResource::collection(
            User::with($this->includes($this->possibleIncludes))
                ->filter($filters)
                ->paginate()
        );
    }

    /**
     * Create a user
     *
     * @group User management
     */
    public function store(StoreUserRequest $request)
    {
        Gate::authorize('store', User::class);
        return new UserResource(User::create($request->mappedAttributes()));
    }

    /**
     * Get a single user
     *
     * @group User management
     */
    public function show(int $userId)
    {
        return new UserResource(User::with($this->includes($this->possibleIncludes))
            ->where('id', $userId)
            ->firstOrFail()
        );
    }

    /**
     * Update a user
     *
     * @group User management
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);
        $user->update($request->mappedAttributes());
        return new UserResource($user);
    }

    /**
     * Replace a user
     *
     * @group User management
     */
    public function replace(ReplaceUserRequest $request, User $user)
    {
        Gate::authorize('replace', $user);
        $user->update($request->mappedAttributes());
        return new UserResource($user);
    }

    /**
     * Delete a user
     *
     * @group User management
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        if ($user->recipes()->exists()) {
            return $this->error('User has associated recipes and cannot be deleted.', 400);
        }
        $user->delete();
        return $this->ok('User successfully deleted');
    }
}
