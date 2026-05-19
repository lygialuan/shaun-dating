<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\MailUnsubscribe;

class UnsubscribeEmailValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email',
            'hash' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                if ($data['hash'] != getHashUnsubscribeFromEmail($data['email'])) {
                    return $validator->errors()->add('hash', __('The hash is not validated.'));
                }

                if (MailUnsubscribe::getByEmail($data['email'])) {
                    return $validator->errors()->add('hash', __('The email is ready for unsubscription.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'email.required' => __('The email is required.'),
            'email.email' => __('The email should be valid.'),
            'hash.required' => __('The hash is required.'),
        ];
    }
}
