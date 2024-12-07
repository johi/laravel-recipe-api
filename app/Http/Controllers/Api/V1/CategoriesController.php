<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;

class CategoriesController extends Controller
{

    /**
     * Get all categories
     *
     * @group Categories
     * @response {"data":[{"type":"recipe","id":"c9ba31bb-0215-4e26-a623-dca00f85763c","attributes":{"title":"Starters"},"links":{"self":"http://localhost:3001/api/v1/categories"}},{"type":"recipe","id":"764f4ba5-450e-4bd1-a988-d6cbc16dbf7e","attributes":{"title":"Main dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}},{"type":"recipe","id":"8859abaa-d617-44c4-939f-93e40148a671","attributes":{"title":"Side dishes"},"links":{"self":"http://localhost:3001/api/v1/categories"}},{"type":"recipe","id":"3625ccd7-5b46-4f8b-b517-895f47875fd3","attributes":{"title":"Dessert"},"links":{"self":"http://localhost:3001/api/v1/categories"}},{"type":"recipe","id":"c68b55d0-eb62-45da-aaa2-38363bcf75f5","attributes":{"title":"Bakery"},"links":{"self":"http://localhost:3001/api/v1/categories"}},{"type":"recipe","id":"528f42c4-9c29-437e-87ca-fb2b2c06a2e6","attributes":{"title":"Drinks"},"links":{"self":"http://localhost:3001/api/v1/categories"}}]}
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

}
