<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\VideoValidation;

class UploadVideoValidate extends BaseFormRequest
{
    public function authorize()
    {
        return setting('feature.ffmpeg_enable');
    }

    public function rules()
    {
        return [
            'file' => ['required', new VideoValidation],
            'is_converted' => ['boolean']
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
        throw new MessageHttpException(__('You can not upload video.'));
    }
}
