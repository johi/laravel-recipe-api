<?php
declare(strict_types=1);

namespace App\Policies\V1;

use App\Models\Recipe;
use App\Models\User;
use App\Permissions\V1\Abilities;

class RecipePolicy
{
    public function store(User $user) : bool
    {
        return $user->tokenCan(Abilities::CREATE_RECIPE) ||
            $user->tokenCan(Abilities::CREATE_OWN_RECIPE);
    }

    public function storeRelated(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::CREATE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return $user->tokenCan(Abilities::CREATE_RECIPE);
    }

    public function update(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::UPDATE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return $user->tokenCan(Abilities::UPDATE_RECIPE);
    }

    public function replace(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::REPLACE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return $user->tokenCan(Abilities::REPLACE_RECIPE);
    }

    public function delete(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::DELETE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return $user->tokenCan(Abilities::DELETE_RECIPE);
    }
}
