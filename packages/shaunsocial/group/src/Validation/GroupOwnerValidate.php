<?php


namespace Packages\ShaunSocial\Group\Validation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupMember;

class GroupOwnerValidate implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        $user = Auth::user();

        $group = Group::findByField('id', $value);
        if (! $group) {
            $fail(__('The group is not found.'));
        }

        if (! GroupMember::checkOwner($user->id, $value)) {
            $fail(__('The group is not found.'));
        }
    }
}
