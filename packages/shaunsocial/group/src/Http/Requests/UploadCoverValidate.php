<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class UploadCoverValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'file' => ['required', new PhotoValidation]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'file.required' => __('The file is required.'),
            'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
        ];
    }
}
