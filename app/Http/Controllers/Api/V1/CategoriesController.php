<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;

class CategoriesController extends Controller
{

    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

}