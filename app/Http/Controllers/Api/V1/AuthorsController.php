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
     * @response {"data":[{"type":"user","id":1,"attributes":{"name":"Admin","email":"admin@example.com","isAdmin":1,"emailVerifiedAt":"2024-10-19T11:06:00.000000Z","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z","included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/1"}]},{"type":"user","id":2,"attributes":{"name":"Mrs. Eulah Schaefer V","email":"mabel.kris@example.com","isAdmin":0,"emailVerifiedAt":"2024-10-19T11:06:00.000000Z","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z","included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/2"}]}],"links":{"first":"http://localhost:3001/api/v1/authors?page=1","last":"http://localhost:3001/api/v1/authors?page=1","prev":null,"next":null},"meta":{"current_page":1,"from":1,"last_page":1,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:3001/api/v1/authors?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"path":"http://localhost:3001/api/v1/authors","per_page":15,"to":11,"total":11}}
     *
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
     * @urlParam id int required Example: 1
     * @queryParam include Include related resources, possible values: recipes Example: include=recipes
     * @response {"data":{"type":"user","id":1,"attributes":{"name":"Admin","email":"admin@example.com","isAdmin":1,"emailVerifiedAt":"2024-10-19T11:06:00.000000Z","createdAt":"2024-10-19T11:06:00.000000Z","updatedAt":"2024-10-19T11:06:00.000000Z","included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/1"}]}}
     */
    public function show(int $userId)
    {
        return new UserResource(
            User::where('id', $userId)
                ->with($this->includes($this->possibleIncludes))
                ->firstOrFail()
        );
    }

}
