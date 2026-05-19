<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

class StoreHashtagValidate extends PageValidate
{
    public function rules()
    {
        return [
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
        ];
    }
}
