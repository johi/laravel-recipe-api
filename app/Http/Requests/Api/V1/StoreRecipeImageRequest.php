<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Intervention\Image\ImageManager;

class StoreRecipeImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048', // 2MB max size
                function ($attribute, $value, $fail) {
                    try {
                        $image = ImageManager::imagick()->read($value->getRealPath());

                        // Validate minimum resolution
                        $minWidth = 800; // Minimum width in pixels
                        $minHeight = 600; // Minimum height in pixels
                        if ($image->width() < $minWidth || $image->height() < $minHeight) {
                            return $fail("The $attribute must have a minimum resolution of $minWidth x $minHeight pixels.");
                        }

                        // Validate aspect ratio (e.g., 4:3)
                        $aspectRatio = 4 / 3;
                        $actualAspectRatio = $image->width() / $image->height();
                        $tolerance = 0.05; // Allow a slight deviation
                        if (abs($actualAspectRatio - $aspectRatio) > $tolerance) {
                            return $fail("The $attribute must have an aspect ratio of approximately 4:3.");
                        }
                    } catch (\Exception $e) {
                        // Handle invalid image files
                        return $fail('The uploaded file could not be processed as an image.');
                    }
                },
            ],
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.required' => 'An image file is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'image.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
