<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class PasswordValidation implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $checked = (Str::length($value) >= config('shaun_core.core.password_length'));
        if (setting('feature.better_password')) {
            $uppercasePasses = (Str::lower($value) !== $value);
            $numericPasses = ((bool) preg_match('/[0-9]/', $value));
            $specialCharacterPasses = ((bool) preg_match('/[^A-Za-z0-9]/', $value));

            $checked = $checked && $uppercasePasses && $numericPasses && $specialCharacterPasses;
        }
        
        if (! $checked) {
            if (setting('feature.better_password')) {
                $fail(__('The :attribute must be at least :max characters and contain at least one uppercase character, one number, and one special character.', ['max' => config('shaun_core.core.password_length')]));
            } else {
                $fail(__('The :attribute must be at least :max characters.', ['max' => config('shaun_core.core.password_length')]));
            }
        }
    }
}
