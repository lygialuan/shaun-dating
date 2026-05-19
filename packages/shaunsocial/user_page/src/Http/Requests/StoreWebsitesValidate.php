<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Illuminate\Support\Facades\Validator;

class StoreWebsitesValidate extends PageValidate
{
    public function rules()
    {
        return [
            'websites' => [
                'nullable',
                function ($attribute, $websites, $fail) {
                    if ($websites) {
                        if (is_array($websites)) {
                            foreach ($websites as $website) {     
                                if (empty($website['link'])) {
                                    return $fail(__('The website should be valid.'));
                                }                   
                                $validator = Validator::make(['link' => trim($website['link'])],[
                                    'link' => 'url'
                                ]);

                                if ($validator->fails()) {
                                    return $fail(__('The website should be valid.'));
                                }
                            }
                        } else {
                            return $fail(__('The website should be valid.'));
                        }
                    }
                },
            ],
        ];
    }
}
