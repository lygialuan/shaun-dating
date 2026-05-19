<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class GetPostPendingValidate extends BaseFormRequest
{
    public function rules()
    {
        $types = array_keys(Group::getPostTypes());
        $sorts = ['last','first'];
        return [
            'id' => [
                'required',
                new GroupAdminValidate()
            ],
            'user_id' => [
                'nullable',
                function ($attribute, $userId, $fail) {
                    $user = User::findByField('id', $userId);
                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }
                }
            ],
            'query' => 'nullable|string',
            'page' => 'integer',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
            'type' => ['nullable', Rule::in($types)],
            'sort' => ['required', Rule::in($sorts)]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'page.integer' => __('The page must number.'),
            'from_date.date_format' => __('The from date format is invalid.'),
            'to_date.date_format' => __('The to date format is invalid.'),
            'type.in' => __('Type is not in list.'),
            'sort.required' => __('The sort is required.'),
            'sort.in' => __('The sort is not in list.'),
        ];
    }
}
