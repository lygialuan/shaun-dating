<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserDownload;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class StoreDownloadValidate extends BaseFormRequest
{
    public function authorize()
    {
        return class_exists('ZipArchive');
    }

    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $userDownload = UserDownload::findByField('user_id', $this->user()->id);
                if ($userDownload && ! $userDownload->canDownload()) {
                    return $validator->errors()->add('user', __('You can not create download your account.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('Please install zip extension'));
    }
}
