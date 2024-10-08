<?php

use App\Http\Controllers\Api\V1\AuthorsRecipesController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\IngredientsController;
use App\Http\Controllers\Api\V1\InstructionsController;
use App\Http\Controllers\Api\V1\RecipesController;
use App\Http\Controllers\Api\V1\AuthorsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('authors', AuthorsController::class);
    Route::apiResource('authors.recipes', AuthorsRecipesController::class)->except(['update']);
    Route::put('authors/{author}/recipes/{recipe}', [AuthorsRecipesController::class, 'replace'])->name('authors.recipes.replace');
    Route::patch('authors/{author}/recipes/{recipe}', [AuthorsRecipesController::class, 'update'])->name('authors.recipes.update');
    Route::apiResource('recipes', RecipesController::class)->except(['update']);
    Route::put('recipes/{recipe}', [RecipesController::class, 'replace'])->name('recipes.replace');
    Route::patch('recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');

    // ingredients
    Route::get('recipes/{recipe}/ingredients', [IngredientsController::class, 'index'])->name('ingredients.index');         // List all ingredients for a specific recipe
    Route::post('recipes/{recipe}/ingredients', [IngredientsController::class, 'store'])->name('ingredients.store');        // Create a new ingredient for a specific recipe
    Route::get('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'show'])->name('ingredients.show'); // Show a specific ingredient for a specific recipe
    Route::put('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'update'])->name('ingredients.update'); // Update a specific ingredient for a specific recipe
    Route::delete('recipes/{recipe}/ingredients/{ingredient}', [IngredientsController::class, 'destroy'])->name('ingredients.destroy'); // Delete a specific ingredient for a specific recipe

    // instructions
    Route::get('recipes/{recipe}/instructions', [InstructionsController::class, 'index'])->name('instructions.index');          // List all instructions for a specific recipe
    Route::post('recipes/{recipe}/instructions', [InstructionsController::class, 'store'])->name('instructions.store');        // Create a new instruction for a specific recipe
    Route::get('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'show'])->name('instructions.show'); // Show a specific instruction for a specific recipe
    Route::put('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'update'])->name('instructions.update'); // Update a specific instruction for a specific recipe
    Route::delete('recipes/{recipe}/instructions/{instruction}', [InstructionsController::class, 'destroy'])->name('instructions.destroy'); // Delete a specific instruction for a specific recipe
});

