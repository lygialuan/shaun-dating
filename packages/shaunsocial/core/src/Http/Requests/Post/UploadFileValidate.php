<?php

namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\FileValidation;

class UploadFileValidate extends BaseFormRequest
{
    public function authorize()
    {
        return setting('post.upload_files_enable');
    }

    public function rules()
    {
        return [
            'file' => ['required', new FileValidation(setting('post.support_files'))],
        ];
    }

    public function messages()
    {
        return [
            'file.required' => __('The file is required.'),
            'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You can not upload file.'));
    }
}
