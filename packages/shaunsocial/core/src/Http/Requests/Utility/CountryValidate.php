<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Traits\RuntimeFormRequest;

class CountryValidate extends BaseFormRequest
{
    use RuntimeFormRequest;

    public static $required = false;
    
    public function rules()
    {
        $rules = [
            'country_id' => [
                'nullable',
                function ($attribute, $countryId, $fail) {
                    if (! $countryId && self::$required) {
                        return $fail(__('The country is required.'));
                    }

                    if ($countryId) {
                        $country = Country::findByField('id', $countryId);
                        if (! $country || ! $country->is_active) {
                            return $fail(__('The country is not found.'));
                        }
                    }
                },
            ],
            'state_id' => [
                'nullable',
                function ($attribute, $stateId, $fail) {
                    if ($stateId) {
                        $state = State::findByField('id', $stateId);
                        if (! $state || ! $state->is_active) {
                            return $fail(__('The state is not found.'));
                        }
                    }
                },
            ],
            'city_id' => [
                'nullable',
                function ($attribute, $cityId, $fail) {
                    if ($cityId) {
                        $city = City::findByField('id', $cityId);
                        if (! $city || ! $city->is_active) {
                            return $fail(__('The city is not found.'));
                        }
                    }
                },
            ],
            'zip_code' => 'string|nullable|max:16',
        ];

        if (self::$required) {
            unset($rules['country_id'][0]);
            $rules['country_id'][] = 'required';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                if (! empty($data['country_id'])) {
                    if (! empty($data['state_id'])) {
                        $state = State::findByField('id', $data['state_id']);
                        if ($data['country_id'] != $state->country_id) {
                            return $validator->errors()->add('state_id', __('The state is not found.'));
                        }

                        if (! empty($data['city_id'])) {
                            $city = City::findByField('id', $data['city_id']);
                            if ($state->id != $city->state_id) {
                                return $validator->errors()->add('city_id', __('The city is not found.'));
                            }
                        } else {
                            if (self::$required) {
                                if ($state->city_count > 0) {
                                    return $validator->errors()->add('state_id', __('The city is required.'));
                                }
                            }
                        }
                    } else {
                        if (self::$required) {
                            $country = Country::findByField('id', $data['country_id']);
                            if ($country->state_count > 0) {
                                return $validator->errors()->add('state_id', __('The state is required.'));
                            }
                        }
                        if (! empty($data['city_id'])) {
                            return $validator->errors()->add('city_id', __('The city id must be empty.'));
                        }
                    }
                } else {
                    if (! empty($data['state_id'])) {
                        return $validator->errors()->add('state_id', __('The state id must be empty.'));
                    }

                    if (! empty($data['city_id'])) {
                        return $validator->errors()->add('city_id', __('The city id must be empty.'));
                    }
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'zip_code.max' => __('The zip code must not be greater than 16 characters.'),
            'country_id.required' => __('The country is required.'),
        ];
    }
}
