<?php

namespace Packages\ShaunSocial\Chatbot\Http\Requests;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreMessageValidate extends BaseFormRequest
{
    use Utility;
    public function rules()
    {
        return [
            'message' => 'required|string|max:4000',
            'context' => 'array'
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                $this->checkPermissionActionLog('chatbot.limit_message_per_day', 'send_message_chatbot', $user);

                $data = $validator->getData();

                $this->checkPermissionHaveValue('chatbot.character_max', strlen($data['message']), $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'message.required' => __('The message is required.'),
        ];
    }
}
