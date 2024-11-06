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
     * This retrieves all users. Please refer to laravel documentation on how
     *  to use pagination: https://laravel.com/docs/11.x/pagination
     *
     * @group User management
     * @queryParam filter[createdAt] Filter by created date (iso: YYYY-MM-DD)  Example: exact date
     *  filter[createdAt]=2024-10-13 or between dates filter[createdAt]=2024-10-13,2024-11-13
     * @queryParam filter[updatedAt] Filter by updated date (iso: YYYY-MM-DD) Example: exact date
     *  filter[updatedAt]=2024-10-13 or between dates filter[updatedAt]=2024-10-13,2024-11-13
     * @queryParam filter[id] Filter by comma separated list of id's Example: filter[id]=1,2,3
     * @queryParam filter[email] Filter by email, with or without using wildcard `*` Example:
     *  filter[email]=`*`@example.com
     * @queryParam filter[name] Filter by name, works with or without use of wildcard Example:
     *  filter[name]=`*`Miller
     * @queryParam include Include related resources, possible values: recipes Example: include=recipes
     * @queryParam sort Data field(s) to sort by: name, email, createdAt, updatedAt. Separate multiple
     *  with commas. Denote descending sort with a minus sign. Example: sort: name,-createdAt
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
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.name string required
     * @bodyParam data.attributes.email string required example: john.doe@example.com
     * @bodyParam data.attributes.isAdmin bool required example: true
     * @bodyParam data.attributes.password string required
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
