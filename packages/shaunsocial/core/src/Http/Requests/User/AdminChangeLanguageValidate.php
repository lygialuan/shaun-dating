<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdminChangeLanguageValidate extends StoreLanguageValidate
{
    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
}
