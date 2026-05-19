<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Subscription;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Subscription;

class GetTransactionValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $id, $fail) use ($viewer) {
                    $subscription = Subscription::findByField('id', $id);
                    $viewer = $this->user();

                    if (! $subscription || $subscription->user_id != $viewer->id) {
                        return $fail(__('The subscription is not found.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
