<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Models\CommentItem;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class DeleteCommentItemValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $itemId, $fail) use ($viewer) {
                    $item = CommentItem::findByField('id', $itemId);
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $item) {
                        return $fail(__('The item is not found.'));
                    }
                    if (! $item->canDelete($viewer->id) && !$isAdmin) {
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
