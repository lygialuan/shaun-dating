<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Hashtag;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Hashtag;

class GetHashtagValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'hashtag' => [
                'required',
                'string',
                function ($attribute, $hashtag, $fail) {
                    if (! checkHashtag($hashtag)) {
                        return $fail(__('The hashtag is not validated.'));
                    }

                    $item = Hashtag::findByField('name', $hashtag);
                    if (!$item || !$item->is_active) {
                        return $fail(__('The hashtag is not found.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'hashtag.required' => __('The hashtag id is required.'),
        ];
    }
}
