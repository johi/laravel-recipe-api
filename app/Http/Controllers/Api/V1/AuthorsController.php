<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\AuthorFilter;
use App\Http\Resources\V1\UserResource;
use App\Models\User;

class AuthorsController extends ApiController
{
    private $possibleIncludes = ['recipes'];

    /**
     * Get all authors
     *
     * @group Authors
     */
    public function index(AuthorFilter $filters)
    {
        return UserResource::collection(
            User::select('users.*')
                ->with($this->includes($this->possibleIncludes))
                ->filter($filters)
                ->whereHas('recipes')
                ->paginate()
        );
    }

    /**
     * Get single author
     *
     * @group Authors
     */
    public function show(int $user_id)
    {
        return new UserResource(
            User::where('id', $user_id)
                ->with($this->includes($this->possibleIncludes))
                ->firstOrFail()
        );
    }

}
