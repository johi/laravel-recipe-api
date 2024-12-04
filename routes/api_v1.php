<?php

use App\Http\Controllers\Api\V1\AuthorRecipesController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\RecipeIngredientsController;
use App\Http\Controllers\Api\V1\RecipeInstructionsController;
use App\Http\Controllers\Api\V1\RecipeImagesController;
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
Route::get('authors/{author}/recipes', [AuthorRecipesController::class, 'index'])
    ->name('authors.recipes.index');
Route::get('recipes/{recipe}/ingredients', [RecipeIngredientsController::class, 'index'])
    ->name('ingredients.index');
Route::get('recipes/{recipe}/ingredients/{ingredient}', [RecipeIngredientsController::class, 'show'])
    ->name('ingredients.show')->scopeBindings();
Route::get('recipes/{recipe}/instructions', [RecipeInstructionsController::class, 'index'])
    ->name('instructions.index');
Route::get('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'show'])
    ->name('instructions.show')->scopeBindings();
Route::get('recipes/{recipe}/images', [RecipeImagesController::class, 'index'])
    ->name('recipes.images.index');
Route::get('recipes/{recipe}/images/{image}', [RecipeImagesController::class, 'show'])
    ->name('recipes.images.show')->scopeBindings();

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
    Route::put('users/{user}', [UsersController::class, 'replace'])->name('users.replace');
    Route::patch('users/{user}', [UsersController::class, 'update'])->name('users.update');

    // ingredients
    Route::post('recipes/{recipe}/ingredients', [RecipeIngredientsController::class, 'store'])
        ->name('ingredients.store');
    Route::put('recipes/{recipe}/ingredients/{ingredient}', [RecipeIngredientsController::class, 'replace'])
        ->name('ingredients.replace')->scopeBindings();
    Route::patch('recipes/{recipe}/ingredients/{ingredient}', [RecipeIngredientsController::class, 'update'])
        ->name('ingredients.update')->scopeBindings();
    Route::delete('recipes/{recipe}/ingredients/{ingredient}', [RecipeIngredientsController::class, 'destroy'])
        ->name('ingredients.destroy')->scopeBindings();

    // instructions
    Route::post('recipes/{recipe}/instructions', [RecipeInstructionsController::class, 'store'])
        ->name('instructions.store');
    Route::put('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'replace'])
        ->name('instructions.replace')->scopeBindings();
    Route::patch('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'update'])
        ->name('instructions.update')->scopeBindings();
    Route::delete('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'destroy'])
        ->name('instructions.destroy')->scopeBindings();
    Route::post('recipes/{recipe}/instructions/update-order', [RecipeInstructionsController::class, 'updateOrder'])
        ->name('instructions.update.order');
    Route::post('recipes/{recipe}/instructions/{instruction}/assign-order', [RecipeInstructionsController::class, 'assignOrder'])
        ->name('instructions.assign.order')->scopeBindings();

    Route::post('recipes/{recipe}/images', [RecipeImagesController::class, 'store'])
        ->name('recipes.images.store');
    Route::delete('recipes/{recipe}/images/{image}', [RecipeImagesController::class, 'destroy'])
        ->name('recipes.images.destroy')->scopeBindings();
});
