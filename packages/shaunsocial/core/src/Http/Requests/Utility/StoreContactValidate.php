<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreContactValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1028',
        ];
    }

    public function withValidator($validator)
    {
        if (setting('spam.contact_enable_recapcha')) {
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
            'name.max' => __('The name must not be greater than 255 characters.'),
            'email.required' => __('The email is required.'),
            'email.email' => __('The email should be valid.'),
            'email.max' => __('The email must not be greater than 255 characters.'),
            'subject.required' => __('The subject is required.'),
            'subject.max' => __('The subject must not be greater than 255 characters.'),
            'message.required' => __('The message is required.'),
            'message.max' => __('The message must not be greater than 1028 characters.'),
        ];
    }
}
