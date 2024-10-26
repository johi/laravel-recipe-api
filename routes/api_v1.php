<?php

use App\Http\Controllers\Api\V1\AuthorsRecipesController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\IngredientsController;
use App\Http\Controllers\Api\V1\InstructionsController;
use App\Http\Controllers\Api\V1\RecipesController;
use App\Http\Controllers\Api\V1\AuthorsController;
use App\Http\Controllers\Api\V1\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
Route::get('recipes', [RecipesController::class, 'index'])->name('recipes.index');
Route::get('recipes/{recipe}', [RecipesController::class, 'show'])->name('recipes.show');
Route::get('authors', [AuthorsController::class, 'index'])->name('authors.index');
Route::get('authors/{author}', [AuthorsController::class, 'show'])->name('authors.show');
Route::get('authors/{author}/recipes', [AuthorsRecipesController::class, 'index'])->name('recipes.index');
Route::get('recipes/{recipe}/ingredients', [IngredientsController::class, 'index'])->name('ingredients.index');
Route::get('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'show'])->name('ingredients.show'); // Show a specific ingredient for a specific recipe

Route::middleware('auth:sanctum')->group(function () {

    Route::post('recipes', [RecipesController::class, 'store'])->name('recipes.store');
    Route::delete('recipes/{recipe}', [RecipesController::class, 'destroy'])->name('recipes.destroy');
    Route::put('recipes/{recipe}', [RecipesController::class, 'replace'])->name('recipes.replace');
    Route::patch('recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');

    Route::apiResource('users', UsersController::class)->except(['update']);
    Route::put('users/{user}', [UsersController::class, 'replace']);
    Route::patch('users/{user}', [UsersController::class, 'update']);

    // ingredients
    Route::post('recipes/{recipe}/ingredients', [IngredientsController::class, 'store'])->name('ingredients.store');        // Create a new ingredient for a specific recipe
    Route::put('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'update'])->name('ingredients.update'); // Update a specific ingredient for a specific recipe
    Route::delete('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'destroy'])->name('ingredients.destroy'); // Delete a specific ingredient for a specific recipe

    // instructions
    Route::get('recipes/{recipe}/instructions', [InstructionsController::class, 'index'])->name('instructions.index');          // List all instructions for a specific recipe
    Route::post('recipes/{recipe}/instructions', [InstructionsController::class, 'store'])->name('instructions.store');        // Create a new instruction for a specific recipe
    Route::get('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'show'])->name('instructions.show'); // Show a specific instruction for a specific recipe
    Route::put('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'update'])->name('instructions.update'); // Update a specific instruction for a specific recipe
    Route::delete('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'destroy'])->name('instructions.destroy'); // Delete a specific instruction for a specific recipe
});

