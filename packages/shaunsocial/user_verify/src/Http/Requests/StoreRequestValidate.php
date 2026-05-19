<?php

namespace Packages\ShaunSocial\UserVerify\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\UserVerify\Models\UserVerifyFile;

class StoreRequestValidate extends BaseFormRequest
{
    public function authorize()
    {
        return ! $this->user()->isVerify();
    }
    
    public function rules()
    {
        $viewer = $this->user();
        return [
            'files' => [
                'required',
                function ($attribute, $files, $fail) use ($viewer) {
                    if (! is_array($files)) {
                        return $fail(__('The files are not in the list.'));
                    }

                    foreach ($files as $fileId) {
                        $file = UserVerifyFile::findByField('id', $fileId);
                        if (! $file || ! $file->canStore($viewer->id)) {
                            return $fail(__('The file is not exist.'));
                        }
                    }
                },
            ],
        ];

    }

    public function messages()
    {
        return [
            'files.required' => __('The files is required.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('Your account has been verified.'));
    }
}
