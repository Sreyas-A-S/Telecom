<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IsImageArray implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $file) {
            if (!$file instanceof UploadedFile) {
                Log::error('IsImageArray: value is not an instance of UploadedFile', ['value' => $file]);
                return false;
            }

            $validator = Validator::make(
                ['image' => $file],
                ['image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']
            );

            if ($validator->fails()) {
                Log::error('IsImageArray: validation failed', ['errors' => $validator->errors()->all()]);
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid image file or an array of image files.';
    }
}
