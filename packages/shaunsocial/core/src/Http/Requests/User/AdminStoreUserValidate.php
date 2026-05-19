<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdminStoreUserValidate extends StoreUserValidate
{
    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
}
