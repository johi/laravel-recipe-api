<?php
declare(strict_types=1);

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory;

    const CATEGORY_STARTERS = 'Starters';
    const CATEGORY_MAIN = 'Main dishes';
    const CATEGORY_SIDE = 'Side dishes';
    const CATEGORY_DESSERT = 'Dessert';
    const CATEGORY_BAKERY = 'Bakery';
    const CATEGORY_DRINKS = 'Drinks';

    public $timestamps = false;

    public static function getCategories(): Collection
    {
        return collect([
            self::CATEGORY_STARTERS,
            self::CATEGORY_MAIN,
            self::CATEGORY_SIDE,
            self::CATEGORY_DESSERT,
            self::CATEGORY_BAKERY,
            self::CATEGORY_DRINKS,
        ]);
    }
    public function recipes() : HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
