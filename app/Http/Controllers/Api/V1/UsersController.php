<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\AuthorFilter;
use App\Http\Requests\Api\V1\ReplaceUserRequest;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Policies\V1\UserPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class UsersController extends ApiController
{
    protected string $policyClass = UserPolicy::class;
    private $possibleIncludes = ['recipes'];

    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            Gate::authorize('store', User::class);
            return new UserResource(User::create($request->mappedAttributes()));
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to create that resource', 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $user_id)
    {
        return new UserResource(
            User::with($this->includes($this->possibleIncludes))
                ->where('id', $user_id)
                ->first()
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int $user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            Gate::authorize('update', $user);
            $user->update($request->mappedAttributes());
            return new UserResource($user);
        } catch (ModelNotFoundException $exception) {
            return $this->error('User cannot be found.', 404);
        } catch (AuthorizationException $exception) {
            return $this->error('You are not authorized to update that resource', 401);
        }
    }

    public function replace(ReplaceUserRequest $request, int $user_id) {
        try {
            $user = User::findOrFail($user_id);
            Gate::authorize('replace', $user);
            $user->update($request->mappedAttributes());
            return new UserResource($user);
        } catch (ModelNotFoundException $exception) {
            return $this->error('User cannot be found.', 404);
        } catch (AuthorizationException $exception) {
            return $this->error('You are not authorized to update that resource', 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            Gate::authorize('delete', $user);
            if ($user->recipes()->exists()) {
                return $this->error('User has associated recipes and cannot be deleted.', 400);
            }
            $user->delete();
            return $this->ok('User successfully deleted');
        } catch (ModelNotFoundException $exception) {
            return $this->error('User cannot be found.', 404);
        } catch (AuthorizationException $exception) {
            return $this->error('You are not authorized to update that resource', 401);
        }
    }
}
