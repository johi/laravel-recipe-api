<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RecipeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->apiResource('recipes', RecipeController::class);

Route::get('/categories', [CategoryController::class, 'index']);
