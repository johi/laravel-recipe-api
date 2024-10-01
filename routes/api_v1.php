<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RecipeController;
use Illuminate\Support\Facades\Route;


Route::apiResource('recipes', RecipeController::class);

Route::get('/categories', [CategoryController::class, 'index']);
