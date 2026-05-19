<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Illuminate\Validation\Rule;

class StorePriceValidate extends PageValidate
{
    public function rules()
    {
        $prices = collect(getPageInfoPriceList());
        return [
            'price' => [
                'required',
                Rule::in($prices->map(function ($item, int $key) {
                    return $item['value'];
                })->all())
            ],
        ];
    }

    public function messages()
    {
        return [
            'price.required' => __('The price is required.'),
            'price.in' => __('The price is not in the list.'),
        ];
    }
}
