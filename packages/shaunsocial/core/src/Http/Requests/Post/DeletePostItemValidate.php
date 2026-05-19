<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\PostItem;

class DeletePostItemValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $itemId, $fail) use ($viewer) {
                    $item = PostItem::findByField('id', $itemId);

                    if (! $item) {
                        return $fail(__('The item is not found.'));
                    }
                    if (! $item->canDelete($viewer->id)) {
                        return $fail(__('You cannot delete this item.'));
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
