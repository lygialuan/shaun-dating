<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Subscription;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\SubscriptionType;

class GetSubscriptionValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();
        return [
            'type' => ['required', Rule::in(array_keys(SubscriptionType::getAllByUser($viewer)))],
            'page' => 'integer',
            'status' => ['required', Rule::in(['all', 'active', 'cancel', 'stop'])]
        ];
    }
    
    public function messages()
    {
        return [
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'status.required' => __('The status is required.'),
            'status.in' => __('The status is not in the list.'),
            'page.integer' => __('The page must number.')
        ];
    }
}
