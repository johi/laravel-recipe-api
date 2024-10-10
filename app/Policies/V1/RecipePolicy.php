<?php

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

    public function update(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::UPDATE_RECIPE)) {
            return true;
        } else if ($user->tokenCan(Abilities::UPDATE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return false;
    }

    public function replace(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::REPLACE_RECIPE)) {
            return true;
        } else if ($user->tokenCan(Abilities::REPLACE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return false;
    }

    public function delete(User $user, Recipe $recipe) : bool
    {
        if ($user->tokenCan(Abilities::DELETE_RECIPE)) {
            return true;
        } else if ($user->tokenCan(Abilities::DELETE_OWN_RECIPE)) {
            return $user->id === $recipe->user_id;
        }
        return false;
    }
}
