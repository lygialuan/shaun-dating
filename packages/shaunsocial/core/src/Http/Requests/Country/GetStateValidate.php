<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Country;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Country;

class GetStateValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'country_id' => [
                'required', 
                'alpha_num',
                function ($attribute, $countryId, $fail) {
                    $country = Country::findByField('id', $countryId);
                    if (! $country || ! $country->is_active) {
                        return $fail(__('The country not found.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'country_id.required' => __('The country id is required.'),
        ];
    }
}
