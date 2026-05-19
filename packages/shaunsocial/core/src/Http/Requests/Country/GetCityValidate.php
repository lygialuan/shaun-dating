<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Country;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\State;

class GetCityValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'state_id' => [
                'required', 
                'alpha_num',
                function ($attribute, $stateId, $fail) {
                    $stateId = State::findByField('id', $stateId);
                    if (! $stateId || ! $stateId->is_active) {
                        return $fail(__('The state not found.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'state_id.required' => __('The state id is required.'),
        ];
    }
}
