<?php

namespace Packages\ShaunSocial\Core\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait RuntimeFormRequest
{
    public static function runFormRequest($value)
    {
        $value = self::getMockedRequestByParameters($value);

        $validator = self::createFrom($value, new self());

        $validator->setContainer(app());

        $validator->prepareForValidation();

        $instance = $validator->getValidatorInstance();

        return $instance;
    }


    protected static function getMockedRequestByParameters($paramters)
    {
        $mockRequest = Request::create('', 'POST');

        $mockRequest->merge($paramters);

        return $mockRequest;
    }
}
