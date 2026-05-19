<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Requests;

use Packages\ShaunSocial\Core\Models\Post;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Http\Requests\Utility\CountryValidate;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Validation\AmountValidation;

class ValidateStoreBootAdvertisingValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) use ($viewer) {
                    $post = Post::findByField('id', $id);
                    if (! $post || ! $post->canBoot($viewer->id)) {
                        return $fail(__('The post is not found.'));
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'gender_id' => [
                'nullable',
                Rule::in(array_keys(Gender::getAllKeyValue() + [0 => '']))
            ],
            'hashtags' => [
                'nullable',
                function ($attribute, $hashtags, $fail) {
                    $check = true;
                    if (! is_array($hashtags)) {
                        return $fail(__('The hashtag is not in the list.'));
                    }
                    if (count($hashtags)) {
                        foreach ($hashtags as $hashtag) {
                            if (! checkHashtag($hashtag))  {
                                $check = false;
                            }
                        }
    
                        if (! $check) {
                            return $fail(__('The hashtag is required.'));
                        }
                    }
                },
            ],
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d',
            'daily_amount' => [
                'required',
                new AmountValidation(),
                'numeric',
                'min:'.setting('shaun_advertising.daily_budget_minimum')
            ],   
            'age_from' => ['nullable', 'numeric', 'min:0'],
            'age_to' => ['nullable', 'numeric', 'min:0']    
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $result = CountryValidate::runFormRequest($this->request->all());
            
            if ($result->fails()) {
                foreach ($result->getMessageBag()->getMessages() as $key => $messages) {
                    $validator->errors()->add($key, $messages[0]);
                }

                return;
            }
        });

        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                
                $data = $validator->getData();
                $start = strtotime(getDateFromTimeZone($data['start'], $user->timezone));
                $end = strtotime(getDateFromTimeZone($data['end'], $user->timezone));
                $currentDate = strtotime(date('Y-m-d'));
                $check = true;
                if ($start < $currentDate) {
                    $check = false;

                    $validator->errors()->add('start', __('The start date cannot be a past date.'));
                }

                if ($start < $currentDate) {
                    $check = false;

                    $validator->errors()->add('end', __('The end date cannot be a past date.'));
                }

                if ($end < $start) {
                    $validator->errors()->add('end', __('The end date must be greater than or equal the start date.'));
                    $check = false;
                }

                if ($data['age_from'] && $data['age_to']) {
                    if ($data['age_to'] < $data['age_from']) {
                        $validator->errors()->add('end', __('The age to must be greater than the age from.'));
                        $check = false;
                    }
                }

                if (! $check) {
                    return;
                }

                $day = subDate($start, $end);
                $amount = $data['daily_amount'] * $day;
                if ($user->getCurrentBalance() < $amount) {
                    throw new MessageHttpException(__("You don't have enough balance."));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 255 characters.'),
            'start.date_format' => __('The start date format is invalid.'),
            'end.date_format' => __('The end date format is invalid.'),
            'amount.min' => __('The amount must be minimum 1.'),
            'age_from.required' => __('The age from is required.'),
            'age_to.required' => __('The age to is required.'),
            'age_from.min' => __('The age from must be greater than 0.'),
            'age_to.min' => __('The age to must be greater than 0.')
        ];
    }
}
