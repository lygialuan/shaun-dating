<?php


namespace Packages\ShaunSocial\Story\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;
use Packages\ShaunSocial\Story\Models\StoryBackground;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Story\Models\StorySong;

class StoreStoryValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $types = StoryItem::getTypes();
        $rules = [
            'type' => ['required', Rule::in($types)],
            'song_id' => [
                'nullable',
                function ($attribute, $songId, $fail) {
                    if ($songId) {
                        $song = StorySong::findByField('id', $songId);

                        if (! $song || ! $song->is_active) {
                            return $fail(__('The song is not found.'));
                        }
                    }
                },
            ]
        ];

        switch ($this->input('type')) {
            case 'text':
                $rules['content'] = 'required|max:1024';
                $rules['content_color'] = [
                    'required',
                    'max:255',
                    'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i',
                ];
                $rules['background_id'] = [
                    'required',
                    function ($attribute, $backgroundId, $fail) {
                        $background = StoryBackground::findByField('id', $backgroundId);

                        if (! $background) {
                            return $fail(__('The background is not found. Please contact admin.'));
                        }
                    },
                ];
                break;
            case 'photo':
                $rules['photo'] = [
                    'required',
                    new PhotoValidation()
                ];
                break;
            case 'video':
                $rules['item_id'] = [
                    'required',
                    function ($attribute, $itemId, $fail) {
                        $item = StoryItem::findByField('id', $itemId);
                        $viewer = $this->user();

                        if (! $item && ! $item->canStore($viewer->id, 'video')) {
                            return $fail(__('The item is not found.'));
                        }
                    },
                ];
        }
        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();

                $this->checkPermissionActionLog('story.max_per_day', 'create_story', $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('The content must not be greater than 1024 characters.'),
            'photo.required' => __('The photo is required.'),
            'photo.uploaded' => __('The photo is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.',
            'background_id.required' => __('The background id is required.'),
            'content_color.required' => __('The content color is required.'),
            'content_color.max' => __('The content color must not be greater than 255 characters.'),
            'content_color.regex' => __('The content color should be valid.'),
            'item_id.required' => __('The video is required.'),
        ];
    }
}
