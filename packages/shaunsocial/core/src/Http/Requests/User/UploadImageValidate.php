<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;

class UploadImageValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'file' => ['required', new PhotoValidation],
        ];
    }

    public function messages()
    {
        return [
            'file.required' => __('The file is required.'),
            'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
        ];
    }
}
