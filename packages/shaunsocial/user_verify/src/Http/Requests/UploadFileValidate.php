<?php

namespace Packages\ShaunSocial\UserVerify\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\FileValidation;

class UploadFileValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'file' => ['required', new FileValidation(setting('user_verify.support_files'))],
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
