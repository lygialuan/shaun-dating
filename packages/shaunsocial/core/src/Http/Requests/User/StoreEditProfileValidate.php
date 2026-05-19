<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Models\Gender;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Requests\Utility\CountryValidate;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreEditProfileValidate extends BaseFormRequest
{
    public function rules()
    {
        $timezoneList = getTimezoneList();
        $rules = [
            'name' => 'string|nullable|max:64',
            'bio' => 'string|nullable|max:'.config('shaun_core.core.user_bio_limit'),
            'about' => 'string|nullable|max:'.config('shaun_core.core.user_about_limit'),
            'birthday' => 'nullable|date_format:Y-m-d|before:today',
            'links' => [
                'nullable',
                function ($attribute, $links, $fail) {
                    if ($links) {
                        if (is_array($links)) {
                            foreach ($links as $link) {     
                                if (empty($link['link'])) {
                                    return $fail(__('The link should be valid. It should be in the format: http://www.example.com'));
                                }                   
                                $validator = Validator::make(['link' => trim($link['link'])],[
                                    'link' => 'url'
                                ]);

                                if ($validator->fails()) {
                                    return $fail(__('The link should be valid. It should be in the format: http://www.example.com'));
                                }
                            }
                        } else {
                            return $fail(__('The link should be valid. It should be in the format: http://www.example.com'));
                        }
                    }
                },
            ],
            'gender_id' => [
                'nullable',
            ],
            'timezone' => [
                'nullable',
                'string',
                Rule::in(array_keys($timezoneList))
            ],
            'privacyField' => [
                'nullable',
                function ($attribute, $privacyField, $fail) {
                    if ($privacyField && ! is_array($privacyField)) {
                        return $fail(__('The privacy field is required.'));
                    }
                },
            ],
            'school_name' => 'string|nullable|max:255',
            'job_title' => 'string|nullable|max:255',
            'company_name' => 'string|nullable|max:255',
            'country_id' => 'integer',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'zip_code' => 'nullable',
            'address' => 'nullable',
        ];

        if (setting('feature.require_birth')) {
            $rules['birthday'] = [
                'nullable', 
                'date_format:Y-m-d',
                'before:today',
                function ($attribute, $birthday, $fail) {
                    if ($birthday && setting('feature.age_restriction')) {
                        $age = Carbon::parse(date('Y-01-01',strtotime($birthday)))->age;
                        if ($age < setting('feature.age_restriction')) {
                            return $fail(__('You must be :1 years of age or older.', ['1' => setting('feature.age_restriction')]));
                        }
                    }
                },
            ];
        }
        if (setting('feature.require_gender')) {
            $rules['gender_id'] = [
                'nullable',
                Rule::in(array_keys(Gender::getAllKeyValue()))
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'bio.max' => __('The bio must not be greater than :max characters.', ['max' => config('shaun_core.core.user_bio_limit')]),
            'location.max' => __('The location must not be greater than 255 characters.'),
            'timezone.required' => __('The timezone is required.'),
            'timezone.in' => __('The timezone is not in the list.'),
            'birthday.required' => __('The birthday is required.'),
            'birthday.date_format' => __('The birthday format is invalid.'),
            'birthday.before' => __('The birthday cannot be a future date.'),
            'gender_id.in' => __('The gender is required.'),
            'gender_id.required' => __('The gender is required.'),
            'about.max' => __('The about must not be greater than :max characters.', ['max' => config('shaun_core.core.user_about_limit')]),
            'privacyField.required' => __('The privacy field is required.')
        ];
    }
}
