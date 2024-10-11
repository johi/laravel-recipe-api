<?php

namespace App\Policies\V1;

use App\Models\User;
use App\Permissions\V1\Abilities;

class UserPolicy
{
    public function store(User $user) : bool
    {
        return $user->tokenCan(Abilities::CREATE_USER);
    }

    public function update(User $user) : bool
    {
        return $user->tokenCan(Abilities::UPDATE_USER);
    }

    public function replace(User $user) : bool
    {
        return $user->tokenCan(Abilities::REPLACE_USER);
    }

    public function delete(User $user) : bool
    {
        return $user->tokenCan(Abilities::DELETE_USER);
    }
}
