<?php

namespace Packages\ShaunSocial\UserVerify\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\UserVerify\Models\UserVerifyFile;

class DeleteFileValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $itemId, $fail) use ($viewer) {
                    $item = UserVerifyFile::findByField('id', $itemId);

                    if (! $item) {
                        return $fail(__('The file is not found.'));
                    }
                    if (! $item->canDelete($viewer->id)) {
                        return $fail(__('You cannot delete this file.'));
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
