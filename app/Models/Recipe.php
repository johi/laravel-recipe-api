<?php
declare(strict_types=1);

namespace App\Models;

use App\Http\Filters\V1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'preparation_time_minutes',
        'servings',
    ];

    protected static function booted()
    {
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

    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients() : HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function instructions() : HasMany
    {
        return $this->hasMany(RecipeInstruction::class)->orderBy('order');
    }

    public function author() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images() : HasMany
    {
        return $this->hasMany(RecipeImage::class);
    }


    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
}
