<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeInstruction extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'description',
        'order',
    ];

    public function recipe() : BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    protected static function booted()
    {
        static::saved(function ($instruction) {
            $instruction->recipe->touch();
        });

        static::deleted(function ($instruction) {
            $instruction->recipe->touch();
        });
    }
}
