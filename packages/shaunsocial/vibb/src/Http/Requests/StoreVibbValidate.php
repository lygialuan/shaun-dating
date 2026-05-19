<?php


namespace Packages\ShaunSocial\Vibb\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Enum\CommentPrivacy;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;
use Packages\ShaunSocial\Vibb\Models\VibbSong;

class StoreVibbValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $commentPrivacies = CommentPrivacy::values();
        $viewer = $this->user();

        $rules = [
            'comment_privacy' => ['required', Rule::in($commentPrivacies)],
            'song_id' => [
                function ($attribute, $songId, $fail) {
                    if ($songId) {
                        $song = VibbSong::findByField('id', $songId);

                        if (! $song || ! $song->is_active) {
                            return $fail(__('The song is not found.'));
                        }
                    }
                }
            ],
            'is_converted' => ['boolean'],
            'item_id' => [
                'required',
                function ($attribute, $itemId, $fail) use ($viewer) {
                    $item = PostItem::findByField('id', $itemId);
                    if (! $item || ! $item->canStore($viewer->id, 'videos')) {
                        return $fail(__('The item is not exist.'));
                    }

                    $subject = $item->getSubject();
                    $extention = $subject->getFile('file_id')->extension;
                    if ($extention != 'mp4') {
                        return $fail(__('The item is not exist.'));
                    }
                },
            ],
            'content_warning_categories' => [
                'nullable',
                function ($attribute, $contentWarningCategories, $fail) {
                    if (! $contentWarningCategories) {
                        return;
                    }
    
                    if (!is_array($contentWarningCategories)) {
                        return $fail(__('The content warning category is not in the list.'));
                    }
                    $check = true;
                    foreach ($contentWarningCategories as $id) {
                        if ($id) {
                            $category = ContentWarningCategory::findByField('id' ,$id);
                            if (!$category || $category->isDeleted()) {
                                $check = false;
                            }
                        }
                    }
    
                    if (!$check) {
                        return $fail(__('The content warning category is not found.'));
                    }
                },
            ]
        ];

        $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                
                $data = $validator->getData();

                $this->checkPermissionHaveValue('post.character_max', strlen($data['content']), $user);

                $this->checkPermissionActionLog('vibb.max_per_day', 'create_vibb', $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.'),
            'content.required' => __('The content is required.'),
            'photos.required' => __('Photos is required.'),
            'parent_id.required' => __('Parent is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => getMaxTextSql(setting('feature.post_character_max'))]),
            'comment_privacy.required' => __('Comment privacy is required.'),
            'comment_privacy.in' => __('Comment privacy is not in list.')
        ];
    }
}
