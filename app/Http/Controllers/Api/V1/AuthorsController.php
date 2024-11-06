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
     * This retrieves users having published recipes only. Please refer to laravel documentation on how
     * to use pagination: https://laravel.com/docs/11.x/pagination
     *
     * @group Authors
     * @queryParam filter[createdAt] Filter by created date (iso: YYYY-MM-DD)  Example: exact date
     * filter[createdAt]=2024-10-13 or between dates filter[createdAt]=2024-10-13,2024-11-13
     * @queryParam filter[updatedAt] Filter by updated date (iso: YYYY-MM-DD) Example: exact date
     * filter[updatedAt]=2024-10-13 or between dates filter[updatedAt]=2024-10-13,2024-11-13
     * @queryParam filter[id] Filter by comma separated list of id's Example: filter[id]=1,2,3
     * @queryParam filter[email] Filter by email, with or without using wildcard `*` Example:
     * filter[email]=`*`@example.com
     * @queryParam filter[name] Filter by name, works with or without use of wildcard Example:
     * filter[name]=`*`Miller
     * @queryParam include Include related resources, possible values: recipes Example: include=recipes
     * @queryParam sort Data field(s) to sort by: name, email, createdAt, updatedAt. Separate multiple
     * with commas. Denote descending sort with a minus sign. Example: sort: name,-createdAt
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
     * @queryParam include Include related resources, possible values: recipes Example: include=recipes
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
