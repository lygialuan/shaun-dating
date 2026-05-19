<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetByIdsValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'ids' => [
                'required',
                function ($attribute, $ids, $fail) {
                    $ids = explode(',', $ids);
                    $ids = array_filter(array_unique($ids));
                    foreach ($ids as $id) {
                        if (! is_numeric($id)) {
                            return $fail(__('The post is not found.'));
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => __('The ids is required.'),
        ];
    }
}
