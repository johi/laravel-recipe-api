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
     * @response {"data":[{"type":"user","id":1,"attributes":{"name":"Admin","email":"admin@example.com","isAdmin":1,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/1"}]},{"type":"user","id":2,"attributes":{"name":"Mrs. Eulah Schaefer V","email":"mabel.kris@example.com","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/2"}]}],"links":{"first":"http://localhost:3001/api/v1/users?page=1","last":"http://localhost:3001/api/v1/users?page=1","prev":null,"next":null},"meta":{"current_page":1,"from":1,"last_page":1,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:3001/api/v1/users?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"path":"http://localhost:3001/api/v1/users","per_page":15,"to":11,"total":11}}
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
     * Creates a user
     *
     * @group User management
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.name string required
     * @bodyParam data.attributes.email string required example: john.doe@example.com
     * @bodyParam data.attributes.isAdmin bool required example: true
     * @bodyParam data.attributes.password string required
     * @response {"data":{"type":"user","id":12,"attributes":{"name":"Test User","email":"test@example.com","isAdmin":null,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/12"}]}}
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
     * @urlParam id int required Example: 1
     * @queryParam include Include related resources, possible values: recipes Example: include=recipes
     * @response {"data":{"type":"user","id":3,"attributes":{"name":"Miss Roxane Barton","email":"okey67@example.org","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/3"}]}}
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
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.name string optional
     * @bodyParam data.attributes.email string optional example: john.doe@example.com
     * @bodyParam data.attributes.isAdmin bool optional example: true
     * @bodyParam data.attributes.password string optional
     * @response {"data":{"type":"user","id":3,"attributes":{"name":"Miss Roxane Barton","email":"okey67@example.org","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/3"}]}}
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
     * @bodyParam data array required
     * @bodyParam data.attributes array required
     * @bodyParam data.attributes.name string required
     * @bodyParam data.attributes.email string required example: john.doe@example.com
     * @bodyParam data.attributes.isAdmin bool required example: true
     * @bodyParam data.attributes.password string required
     * @response {"data":{"type":"user","id":3,"attributes":{"name":"Miss Roxane Barton","email":"okey67@example.org","isAdmin":0,"included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/3"}]}}
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
     * @response {"data":[],"message":"User successfully deleted","status":200}
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
