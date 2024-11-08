<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
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
        // Hook into the saved event
        static::saved(function ($ingredient) {
            // Update the related recipe's updated_at timestamp
            $ingredient->recipe->touch();
        });

        // Hook into the deleted event (if needed)
        static::deleted(function ($ingredient) {
            $ingredient->recipe->touch();
        });
    }
}

