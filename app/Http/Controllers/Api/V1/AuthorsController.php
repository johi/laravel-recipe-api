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
     * @queryParam filter[createdAt] Filter by exact (single value) or between created iso-date (comma separated) Example: 2024-10-13,2024-11-13
     * @queryParam filter[updatedAt] Filter by exact (single value) or between created iso-date (comma separated) Example: 2024-10-13,2024-11-13
     * @queryParam filter[id] Filter by comma separated list of id's Example: 1,2,3
     * @queryParam filter[email] Filter by email, with or without using wildcard `*` Example:`*`@example.com
     * @queryParam filter[name] Filter by name, works with or without use of wildcard Example:`*`Miller
     * @queryParam include Include related resources, possible values: recipes Example: recipes
     * @queryParam sort Data field(s) to sort by: name, email, createdAt, updatedAt. Separate multiple
     * with commas. Denote descending sort with a minus sign. Example: name,-createdAt
     * @response {"data":[{"type":"user","id":"18034e5b-e428-4af1-aca8-9e177ad95020","attributes":{"name":"Rafaela Beahan","email":"scot23@example.net","isAdmin":false,"emailVerifiedAt":"2024-12-05T13:02:10.000000Z","createdAt":"2024-12-05T13:02:10.000000Z","updatedAt":"2024-12-05T13:02:10.000000Z","included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/18034e5b-e428-4af1-aca8-9e177ad95020"}]},{"type":"user","id":"464ebf33-f574-4cd6-94d5-157def72a75e","attributes":{"name":"Beth Kuhlman","email":"myra.schumm@example.com","isAdmin":false,"emailVerifiedAt":"2024-12-05T13:02:10.000000Z","createdAt":"2024-12-05T13:02:10.000000Z","updatedAt":"2024-12-05T13:02:10.000000Z","included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/464ebf33-f574-4cd6-94d5-157def72a75e"}]}],"links":{"first":"http://localhost:3001/api/v1/authors?page=1","last":"http://localhost:3001/api/v1/authors?page=1","prev":null,"next":null},"meta":{"current_page":1,"from":1,"last_page":1,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:3001/api/v1/authors?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"path":"http://localhost:3001/api/v1/authors","per_page":15,"to":10,"total":10}}
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
     * @queryParam include Include related resources, possible values: recipes Example: include=recipes
     * @response {"data":{"type":"user","id":"18034e5b-e428-4af1-aca8-9e177ad95020","attributes":{"name":"Rafaela Beahan","email":"scot23@example.net","isAdmin":false,"emailVerifiedAt":"2024-12-05T13:02:10.000000Z","createdAt":"2024-12-05T13:02:10.000000Z","updatedAt":"2024-12-05T13:02:10.000000Z","included":[]},"links":[{"self":"http://localhost:3001/api/v1/authors/18034e5b-e428-4af1-aca8-9e177ad95020"}]}}
     */
    public function show(User $author)
    {
        return new UserResource(
            User::where('id', $author->id)
                ->with($this->includes($this->possibleIncludes))
                ->firstOrFail()
        );
    }

}
