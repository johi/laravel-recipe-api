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
            $table->string('title');
        });

        foreach (Category::getCategories() as $category) {
            $newCategory = new Category();
            $newCategory['title'] = $category;
            $newCategory->save();
        }

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('preparation_time_minutes');
            $table->unsignedInteger('servings');
            $table->string('image_url');
            $table->timestamps();
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes');
            $table->string('title');
            $table->float('quantity');
            $table->string('unit');
            $table->timestamps();
        });

        Schema::create('instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes');
            $table->text('description');
            $table->unsignedInteger('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructions');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('categories');
    }
};
