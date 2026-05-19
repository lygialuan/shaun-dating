<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupRule;

class StoreRuleOrderValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'group_id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $group = Group::findByField('id', $id);
                    if (! $group) {
                        return $fail(__('The group is not found.'));
                    }
                }
            ],
            'orders' => [
                'required',
                function ($attribute, $orders, $fail) {
                    $id = $this->get('group_id');
                    if ($id) {
                        if (! is_array($orders)) {
                            return $fail(__('The orders are not in the list.'));
                        }

                        if (! count($orders)) {
                            return $fail(__('The order is not exist.'));
                        }

                        foreach ($orders as $order) {
                            $rule = GroupRule::findByField('id', $order);
                            if (! $rule || $rule->group_id != $id) {
                                return $fail(__('The order is not exist.'));
                            }
                        }
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => __('The group id is required.')
        ];
    }
}
