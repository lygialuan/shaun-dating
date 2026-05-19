<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;

class UserNameValidate implements ValidationRule
{
    protected $message = null;

    public function validate($attribute, $value, $fail): void
    {
        if (! ((bool) preg_match(config('shaun_core.regex.user_name'), $value))) {
            if ($this->message) {
                $fail($this->message);
            } else {
                $fail(__('Between 5 and 30 characters for the username is acceptable, can be any combination of letters, numbers, or some special characters. Usernames can contain letters (a-z), (A-Z), numbers (0-9), periods (.), or the underscore character (_).'));
            }
        }
    }
}
