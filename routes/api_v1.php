<?php

use App\Http\Controllers\Api\V1\AuthorsRecipesController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\IngredientsController;
use App\Http\Controllers\Api\V1\InstructionsController;
use App\Http\Controllers\Api\V1\RecipesController;
use App\Http\Controllers\Api\V1\AuthorsController;
use App\Http\Controllers\Api\V1\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoriesController::class, 'index'])
    ->name('categories.index');
Route::get('recipes', [RecipesController::class, 'index'])
    ->name('recipes.index');
Route::get('recipes/{recipe}', [RecipesController::class, 'show'])
    ->name('recipes.show');
Route::get('authors', [AuthorsController::class, 'index'])
    ->name('authors.index');
Route::get('authors/{author}', [AuthorsController::class, 'show'])
    ->name('authors.show');
Route::get('authors/{author}/recipes', [AuthorsRecipesController::class, 'index'])
    ->name('recipes.index');
Route::get('recipes/{recipe}/ingredients', [IngredientsController::class, 'index'])
    ->name('ingredients.index');
Route::get('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'show'])
    ->name('ingredients.show')->scopeBindings();
Route::get('recipes/{recipe}/instructions', [InstructionsController::class, 'index'])
    ->name('instructions.index');
Route::get('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'show'])
    ->name('instructions.show')->scopeBindings();

Route::middleware('auth:sanctum')->group(function () {

    Route::post('recipes', [RecipesController::class, 'store'])
        ->name('recipes.store');
    Route::delete('recipes/{recipe}', [RecipesController::class, 'destroy'])
        ->name('recipes.destroy');
    Route::put('recipes/{recipe}', [RecipesController::class, 'replace'])
        ->name('recipes.replace');
    Route::patch('recipes/{recipe}', [RecipesController::class, 'update'])
        ->name('recipes.update');

    Route::apiResource('users', UsersController::class)->except(['update']);
    Route::put('users/{user}', [UsersController::class, 'replace']);
    Route::patch('users/{user}', [UsersController::class, 'update']);

    // ingredients
    Route::post('recipes/{recipe}/ingredients', [IngredientsController::class, 'store'])
        ->name('ingredients.store');
    Route::put('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'replace'])
        ->name('ingredients.replace')->scopeBindings();
    Route::patch('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'update'])
        ->name('ingredients.update')->scopeBindings();
    Route::delete('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'destroy'])
        ->name('ingredients.destroy')->scopeBindings();

    // instructions
    Route::post('recipes/{recipe}/instructions', [InstructionsController::class, 'store'])
        ->name('instructions.store');
    Route::put('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'replace'])
        ->name('instructions.replace')->scopeBindings();
    Route::patch('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'update'])
        ->name('instructions.update')->scopeBindings();
    Route::delete('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'destroy'])
        ->name('instructions.destroy')->scopeBindings();
    Route::post('recipes/{recipe}/instructions/update-order', [InstructionsController::class, 'updateOrder'])
        ->name('instructions.update.order');
    Route::post('recipes/{recipe}/instructions/{instruction}/assign-order', [InstructionsController::class, 'assignOrder'])
        ->name('instructions.assign.order')->scopeBindings();
});
