<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instruction extends Model
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
        // Hook into the saved event
        static::saved(function ($instruction) {
            // Update the related recipe's updated_at timestamp
            $instruction->recipe->touch();
        });

        // Hook into the deleted event (if needed)
        static::deleted(function ($instruction) {
            $instruction->recipe->touch();
        });
    }
}
