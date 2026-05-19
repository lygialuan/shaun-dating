<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Requests;

use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetDetailAdvertisingValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) use ($viewer) {
                    $advertising = Advertising::findByField('id', $id);
                    if (! $advertising || ! $advertising->isOwner($viewer->id)) {
                        return $fail(__('The advertising is not found.'));
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.')
        ];
    }
}
