<?php
declare(strict_types=1);

namespace App\Http\Filters\V1;

class RecipeFilter extends QueryFilter
{
    protected $sortable = [
        'title',
        'preparationTimeMinutes' => 'preparation_time_minutes',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    ];

    public function createdAt($value)
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            return $this->builder->whereBetween('created_at', $dates);
        }

        return $this->builder->whereDate('created_at', $value);
    }

    public function updatedAt($value)
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            return $this->builder->whereBetween('updated_at', $dates);
        }

        return $this->builder->whereDate('updated_at', $value);
    }

    public function preparationTimeMinutes($value)
    {
        $minutes = explode(',', $value);

        if (count($minutes) > 1) {
            return $this->builder->whereBetween('preparation_time_minutes', $minutes);
        }
        return $this->builder->where('preparation_time_minutes', '<=', $value);
    }

    public function title($value) {
        $likeStr = str_replace('*', '%', $value);
        return $this->builder->where('title', 'like', $likeStr);
    }

    public function category($value)
    {
        return $this->builder->whereHas('category', function ($query) use ($value) {
            $likeStr = str_replace('*', '%', strtolower($value));
            $query->whereRaw('LOWER(title) LIKE ?', [$likeStr]);
        });
    }

    public function ingredient($value)
    {
        return $this->builder->whereHas('ingredients', function ($query) use ($value) {
            $likeStr = str_replace('*', '%', strtolower($value));
            $query->whereRaw('LOWER(title) LIKE ?', [$likeStr]);
        });
    }
}
