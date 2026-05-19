<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class PhotoValidation implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $validation = Validator::make([$attribute => $value], [
            $attribute => new FileValidation(config('shaun_core.validation.photo')),
        ]);

        if ($validation->fails()) {
            $fail($validation->getMessageBag()->first());
        }
    }
}
