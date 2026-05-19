<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;

class PhoneValidation implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        if (! validatePhoneNumber($value)) {
            $fail(__('The phone number is in an invalid format.'));
        }
    }
}
