<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Invite;

use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreInviteValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        return [
            'emails' => [
                'required',
                'string',
                function ($attribute, $emails, $fail) {
                    $user = $this->user();

                    $emails = Str::of($emails)->explode(',')->filter(function ($value) {
                        return !empty($value);
                    });
                    
                    if (! $emails->count()) {
                        return $fail(__('The email should be valid.'));
                    }
                    
                    foreach ($emails as $email) {                        
                        $validator = Validator::make(['email' => trim($email)],[
                            'email' => 'email|max:255',
                        ]);

                        if ($validator->fails()) {
                            return $fail(__('The email should be valid.'));
                        }
                    }

                    if ($emails->count() > getInviteMax()) {
                        return $fail(__('The maximum number of emails sent each time is :max', ['max' => getInviteMax()]).'.');
                    }

                    if (! $this->checkInviteLimit($user, $emails->count())) {
                        return $fail(__('Number of invitations that can be sent per day is :max', ['max' => getInviteLimit()]).'.');
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (setting('spam.invite_email_enable_recapcha')) {
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
            'emails.required' => __('The email is required.')
        ];
    }
}
