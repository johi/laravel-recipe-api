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

#categories
Route::get('/categories', [CategoriesController::class, 'index'])
    ->name('categories.index');
#recipes
Route::get('recipes', [RecipesController::class, 'index'])
    ->name('recipes.index');
Route::get('recipes/{recipe:uuid}', [RecipesController::class, 'show'])
    ->name('recipes.show');
#authors
Route::get('authors', [AuthorsController::class, 'index'])
    ->name('authors.index');
Route::get('authors/{author:uuid}', [AuthorsController::class, 'show'])
    ->name('authors.show');
Route::get('authors/{author:uuid}/recipes', [AuthorRecipesController::class, 'index'])
    ->name('authors.recipes.index');
#ingredients
Route::get('recipes/{recipe:uuid}/ingredients', [RecipeIngredientsController::class, 'index'])
    ->name('recipes.ingredients.index');
Route::get('recipes/{recipe:uuid}/ingredients/{ingredient}', [RecipeIngredientsController::class, 'show'])
    ->name('recipes.ingredients.show')->scopeBindings();
#instructions
Route::get('recipes/{recipe}/instructions', [RecipeInstructionsController::class, 'index'])
    ->name('recipes.instructions.index');
Route::get('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'show'])
    ->name('recipes.instructions.show')->scopeBindings();
#images
Route::get('recipes/{recipe:uuid}/images', [RecipeImagesController::class, 'index'])
    ->name('recipes.images.index');
Route::get('recipes/{recipe:uuid}/images/{image:uuid}', [RecipeImagesController::class, 'show'])
    ->name('recipes.images.show')->scopeBindings();

Route::middleware('auth:sanctum')->group(function () {
    #recipes
    Route::post('recipes', [RecipesController::class, 'store'])
        ->name('recipes.store');
    Route::delete('recipes/{recipe:uuid}', [RecipesController::class, 'destroy'])
        ->name('recipes.destroy');
    Route::put('recipes/{recipe:uuid}', [RecipesController::class, 'replace'])
        ->name('recipes.replace');
    Route::patch('recipes/{recipe:uuid}', [RecipesController::class, 'update'])
        ->name('recipes.update');
    #users
    Route::apiResource('users', UsersController::class)->except(['update', 'show', 'destroy']);
    Route::get('users/{user:uuid}', [UsersController::class, 'show'])->name('users.show');
    Route::delete('users/{user:uuid}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::put('users/{user:uuid}', [UsersController::class, 'replace'])->name('users.replace');
    Route::patch('users/{user:uuid}', [UsersController::class, 'update'])->name('users.update');
    #ingredients
    Route::post('recipes/{recipe:uuid}/ingredients', [RecipeIngredientsController::class, 'store'])
        ->name('recipes.ingredients.store');
    Route::put('recipes/{recipe:uuid}/ingredients/{ingredient:uuid}', [RecipeIngredientsController::class, 'replace'])
        ->name('recipes.ingredients.replace')->scopeBindings();
    Route::patch('recipes/{recipe:uuid}/ingredients/{ingredient:uuid}', [RecipeIngredientsController::class, 'update'])
        ->name('recipes.ingredients.update')->scopeBindings();
    Route::delete('recipes/{recipe:uuid}/ingredients/{ingredient:uuid}', [RecipeIngredientsController::class, 'destroy'])
        ->name('recipes.ingredients.destroy')->scopeBindings();
    #instructions
    Route::post('recipes/{recipe}/instructions', [RecipeInstructionsController::class, 'store'])
        ->name('recipes.instructions.store');
    Route::put('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'replace'])
        ->name('recipes.instructions.replace')->scopeBindings();
    Route::patch('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'update'])
        ->name('recipes.instructions.update')->scopeBindings();
    Route::delete('recipes/{recipe}/instructions/{instruction}', [RecipeInstructionsController::class, 'destroy'])
        ->name('recipes.instructions.destroy')->scopeBindings();
    Route::post('recipes/{recipe}/instructions/update-order', [RecipeInstructionsController::class, 'updateOrder'])
        ->name('recipes.instructions.update.order');
    Route::post('recipes/{recipe}/instructions/{instruction}/assign-order', [RecipeInstructionsController::class, 'assignOrder'])
        ->name('recipes.instructions.assign.order')->scopeBindings();
    #images
    Route::post('recipes/{recipe:uuid}/images', [RecipeImagesController::class, 'store'])
        ->name('recipes.images.store');
    Route::delete('recipes/{recipe:uuid}/images/{image:uuid}', [RecipeImagesController::class, 'destroy'])
        ->name('recipes.images.destroy')->scopeBindings();
});
