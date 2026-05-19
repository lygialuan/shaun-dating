<?php


namespace Packages\ShaunSocial\Core\Validation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class AmountValidation implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $decimal = config('shaun_core.core.decimal');
        $rule = 'min:0.01|decimal:0,'.$decimal;
        $messages = [
            $attribute.'.min' => __('The amount must be greater than 0.01.'),
            $attribute.'.decimal' => __('The amount field must have 0-:number decimal places.',[
                'number' => $decimal
            ]),
        ];

        if (! $decimal) {
            $rule = 'min:1|decimal:0';
            $messages[$attribute.'.min'] = __('The amount must be greater than 1.');
            $messages[$attribute.'.decimal'] = __('The amount field must have 0 decimal places.');
        }
        $validation = Validator::make([$attribute => $value], [
            $attribute => $rule,
        ], $messages);

        if ($validation->fails()) {
            $fail($validation->getMessageBag()->first());
        }
    }
}
