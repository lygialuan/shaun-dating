<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class PasswordVerifyValidation implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $viewer = auth()->user();
        if (! $viewer) {
            $fail(__('Not authenticated.'));
        }

        $user = $viewer;
        if ($viewer->isPage()) {
            $user = $viewer->getParent();
        }
        if (! Hash::check($value, $user->password)) {
            $fail(__('The password is incorrect.'));
        }
    }
}
