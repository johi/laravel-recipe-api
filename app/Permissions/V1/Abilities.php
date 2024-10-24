<?php
declare(strict_types=1);

namespace App\Permissions\V1;
use App\Models\User;

final class Abilities
{
    public const CREATE_RECIPE = 'recipe:create';
    public const UPDATE_RECIPE = 'recipe:update';
    public const REPLACE_RECIPE = 'recipe:replace';
    public const DELETE_RECIPE = 'recipe:delete';

    public const CREATE_OWN_RECIPE = 'recipe:own:create';
    public const UPDATE_OWN_RECIPE = 'recipe:own:update';
    public const REPLACE_OWN_RECIPE = 'recipe:own:replace';
    public const DELETE_OWN_RECIPE = 'recipe:own:delete';

    public const CREATE_USER = 'user:create';
    public const UPDATE_USER = 'user:update';
    public const REPLACE_USER = 'user:replace';
    public const DELETE_USER = 'user:delete';

    public static function getAbilities(User $user): array
    {
        if ($user->is_admin) {
            return [
                self::CREATE_RECIPE,
                self::UPDATE_RECIPE,
                self::REPLACE_RECIPE,
                self::DELETE_RECIPE,
                self::CREATE_USER,
                self::UPDATE_USER,
                self::REPLACE_USER,
                self::DELETE_USER,
            ];
        } else {
            return [
                self::CREATE_OWN_RECIPE,
                self::UPDATE_OWN_RECIPE,
                self::REPLACE_OWN_RECIPE,
                self::DELETE_OWN_RECIPE,
            ];
        }
    }
}
