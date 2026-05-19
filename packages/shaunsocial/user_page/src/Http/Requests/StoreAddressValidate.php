<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\Utility\CountryValidate;

class StoreAddressValidate extends PageValidate
{
    public function rules()
    {
        return [
            'address' => 'string|nullable|max:255',
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

        return $validator;
    }

    public function messages()
    {
        return [
            'address.max' => __('The address must not be greater than 255 characters.'),
        ];
    }
}
