<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'title',
        'quantity',
        'unit',
    ];

    public function recipe() : BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
    protected static function booted()
    {
        static::saved(function ($ingredient) {
            $ingredient->recipe->touch();
        });

        static::deleted(function ($ingredient) {
            $ingredient->recipe->touch();
        });

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }
    public function getRouteKeyName()
    {
        return 'uuid';
    }

}

