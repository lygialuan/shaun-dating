<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class GetProfilePackageValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $id, $fail) use ($viewer) {
                    $user = User::findByField('id', $id);
                    
                    if (! $user || ! $user->canSubscriber($viewer->id)) {
                        return $fail(__('The user is not found.')); 
                    }
                },
            ]
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
