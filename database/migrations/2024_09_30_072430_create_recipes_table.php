<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->index('uuid');
        });

        foreach (Category::getCategories() as $category) {
            $newCategory = new Category();
            $newCategory['title'] = $category;
            $newCategory->save();
        }

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('preparation_time_minutes');
            $table->unsignedInteger('servings');
            $table->timestamps();
            $table->index('uuid');
        });

        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->string('title');
            $table->float('quantity');
            $table->string('unit');
            $table->timestamps();
            $table->index('uuid');
        });

        Schema::create('recipe_instructions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->text('description');
            $table->unsignedInteger('order');
            $table->timestamps();
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_instructions');
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('categories');
    }
};
