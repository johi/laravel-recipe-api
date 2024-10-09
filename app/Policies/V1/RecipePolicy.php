<?php

namespace App\Policies\V1;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Recipe $recipe) {
        // TODO check for token ability
        return $user->id === $recipe->user_id;
    }
}
