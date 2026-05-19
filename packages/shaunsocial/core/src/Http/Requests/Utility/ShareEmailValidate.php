<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Traits\Utility;

class ShareEmailValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        return [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num',
            'emails' => [
                'required',
                'string',
                function ($attribute, $emails, $fail) {
                    $emails = Str::of($emails)->explode(',')->filter(function ($value) {
                        return !empty($value);
                    });
                    
                    if (! $emails->count()) {
                        return $fail(__('The email should be valid.'));
                    }

                    foreach ($emails as $email) {            
                        $validator = Validator::make(['email' => trim($email)],[
                            'email' => 'email|max:255'
                        ]);

                        if ($validator->fails()) {
                            return $fail(__('The email should be valid.'));
                        }
                    }

                    if ($emails->count() > getShareEmailMax()) {
                        return $fail(__('The maximum number of emails sent each time is :max', ['max' => getShareEmailMax()]).'.');
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewerId = $this->user()->id;
                $isAdmin = $this->user()->isModerator();
                $data = $validator->getData();
                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportShareEmail')) {
                    return $validator->errors()->add('subject', __('The subject does not support share email.'));
                }

                if (method_exists($subject, 'canShareEmail') && ! $subject->canShareEmail($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot share.'));
                }

                if (setting('spam.share_email_enable_recapcha')) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'subject_type.required' => __('Subject is required.'),
            'subject_id.required' => __('Subject id is required.'),
            'emails.required' => __('Email is required.')
        ];
    }
}
