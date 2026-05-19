<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Http\Requests\Utility\CountryValidate;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;
use Packages\ShaunSocial\Core\Validation\PhoneValidation;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class SignupValidate extends BaseFormRequest
{
    use Utility;
    
    public function authorize()
    {
        return setting('feature.enable_signup');
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:64',
            'user_name' => [
                'required',
                'string',
                new UserNameValidate(),
                'max:128',
                function ($attribute, $userName, $fail) {
                    if (checkUsernameBan($userName)) {
                        return $fail(__('The username has been banned.'));
                    }

                    $user = User::findByField('user_name', $userName);

                    if ($user) {
                        return $fail(__('The username already exists.'));
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $email, $fail) {
                    if (checkEmailBan($email)) {
                        return $fail(__('The email has been banned.'));
                    }

                    $user = User::findByField('email', $email);

                    if ($user) {
                        return $fail(__('The email already exists.'));
                    }
                },
            ],
            'password' => ['required', 'string', new PasswordValidation()],
            'ref_code' => ['nullable', 'max:255']
        ];

        if (setting('feature.require_birth')) {
            $rules['birthday'] = [
                'required', 
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
                'required',
                Rule::in(array_keys(Gender::getAllKeyValue()))
            ];
        }

        if (setting('feature.phone_verify')) {
            $rules['phone_number'] = [
                'required', 
                new PhoneValidation(),
                function ($attribute, $phoneNumber, $fail) {
                    if ($phoneNumber) {
                        $user = User::checkExistPhoneNumber($phoneNumber);
                        if ($user) {
                            return $fail(__('The phone number already exists.'));
                        }
                    }
                },
            ];
        }

        if (setting('feature.invite_only')) {
            $rules['ref_code'] = [
                'required', 
                function ($attribute, $refCode, $fail) {
                    $user = User::findByField('ref_code', $refCode);
                    if (! $user) {
                        return $fail(__('Invalid invitation code.'));
                    }
                },
            ];
        }

        return $rules;
    }

    public function prepareForValidation()
    {
        parent::prepareForValidation();
        
        $phoneNumber = $this->input('phone_number');
        if ($phoneNumber) {
            $this->merge([
                'phone_number' => str_replace(' ', '', $phoneNumber)
            ]);
        }
    }

    public function withValidator($validator)
    {
        if (setting('feature.require_location')) {
            $validator->after(function ($validator) {
                CountryValidate::$required = true;
                $result = CountryValidate::runFormRequest($this->request->all());
                
                if ($result->fails()) {
                    foreach ($result->getMessageBag()->getMessages() as $key => $messages) {
                        $validator->errors()->add($key, $messages[0]);
                    }

                    return;
                }
            });
        }
        
        if (setting('spam.signup_enable_recapcha')) {
            if (! $validator->fails()) {
                $validator->after(function ($validator) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                });
            }
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'user_name.required' => __('The username is required.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
            'email.required' => __('The email is required.'),
            'email.email' => __('The email should be valid.'),
            'email.max' => __('The email must not be greater than 255 characters.'),
            'password.required' => __('The password is required.'),
            'ref_code.max' => __('The reference code must not be greater than 255 characters.'),
            'birthday.required' => __('The birthday is required.'),
            'birthday.date_format' => __('The birthday format is invalid.'),
            'birthday.before' => __('The birthday cannot be a future date.'),
            'gender_id.required' => __('The gender is required.'),
            'gender_id.in' => __('The gender is required.'),
            'phone_number.required' => __('The phone number is required.'),
            'ref_code.required' => __('The invitation code is required.')
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot sign up this site.'));
    }
}
