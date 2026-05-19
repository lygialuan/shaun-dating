<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class SlugValidate implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $validSlug = Str::of($value)->slug('-');

        if ($validSlug != $value) {
            $fail(__('The slug must be validate formatted.'));
        }
    }
}
