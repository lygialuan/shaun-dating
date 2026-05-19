<?php


namespace Packages\ShaunSocial\Core\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Packages\ShaunSocial\Core\Traits\ApiResponser;

class BaseFormRequest extends FormRequest
{
    use ApiResponser;

    public function prepareForValidation()
    {
        if ($this->route()) {
            $this->merge($this->route()->parameters());
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $result = $validator->getMessageBag()->toArray();
        foreach ($result as $field => $messages) {
            $result[$field] = $messages[0];
        }
        throw new HttpResponseException($this->errorValidateRespone($result));
    }
}
