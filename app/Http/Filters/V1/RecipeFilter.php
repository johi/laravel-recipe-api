<?php

namespace App\Http\Filters\V1;

class RecipeFilter extends QueryFilter
{
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
}
