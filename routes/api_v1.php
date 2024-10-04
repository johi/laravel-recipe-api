<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->apiResource('recipes', RecipeController::class);
Route::middleware('auth:sanctum')->apiResource('users', UsersController::class);
Route::get('/categories', [CategoryController::class, 'index']);
