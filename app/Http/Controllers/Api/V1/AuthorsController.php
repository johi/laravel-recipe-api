<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\AuthorFilter;
use App\Http\Resources\V1\UserResource;
use App\Models\User;

class AuthorsController extends ApiController
{
    private $possibleIncludes = ['recipes'];

    public function index(AuthorFilter $filters)
    {
        return UserResource::collection(
            User::select('users.*')
                ->with($this->includes($this->possibleIncludes))
                ->join('recipes', 'recipes.user_id', '=', 'users.id')
                ->filter($filters)
                ->distinct()
                ->paginate()
        );
    }

    public function show(int $user_id)
    {
        return new UserResource(
            User::where('id', $user_id)
                ->with($this->includes($this->possibleIncludes))
                ->first()
        );
    }

}
