<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Requests;

use Packages\ShaunSocial\Core\Models\Post;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingAgeType;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Http\Requests\Utility\CountryValidate;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreEditAdvertisingValidate extends BaseFormRequest
{
    use Utility;

    public function rules()
    {
        $viewer = $this->user();
        $types = array_keys(Post::getTypes());
        unset($types['share']);
        $rules = [
            'id' => [
                'required',
                function ($attribute, $id, $fail) use ($viewer) {
                    if ($id) {
                        $advertising = Advertising::findByField('id', $id);
                        if (! $advertising || ! $advertising->canEdit($viewer->id)) {
                            return $fail(__('The advertising is not found.'));
                        }
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in($types)],
            'gender_id' => [
                'nullable',
                Rule::in(array_keys(Gender::getAllKeyValue() + [0 => '']))
            ],
            'hashtags' => [
                'nullable',
                function ($attribute, $hashtags, $fail) {
                    $check = true;
                    if (! is_array($hashtags)) {
                        return $fail(__('The hashtag is not in the list.'));
                    }
                    if (count($hashtags)) {
                        foreach ($hashtags as $hashtag) {
                            if (! checkHashtag($hashtag))  {
                                $check = false;
                            }
                        }
    
                        if (! $check) {
                            return $fail(__('The hashtag is required.'));
                        }
                    }
                },
            ],
            'age_from' => ['nullable', 'numeric', 'min:0'],
            'age_to' => ['nullable', 'numeric', 'min:0'],
        ];

        switch ($this->input('age_type')) {
            case AdvertisingAgeType::RANGE->value:
                $rules['age_to'] = ['required', 'numeric', 'min:0'];
                break;
        }

        $viewer = $this->user();
        $type = $this->input('type');
        $id = $this->input('id');

        $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));
        
        switch ($this->input('type')) {
            case 'text':
                $this->merge(['items' => []]);
                $rules['content'] = ['required'];
                $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));

                break;
            case 'photo':
            case 'link':
            case 'video':
                $rules['items'] = [
                    'required',
                    function ($attribute, $items, $fail) use ($viewer, $type, $id) {
                        switch ($type) {
                            case 'photo':
                                $subjectType = 'storage_files';
                                break;
                            case 'link':
                                $subjectType = 'links';
                                break;
                            case 'video':
                                $subjectType = 'videos';
                                break;
                        }

                        if (! is_array($items)) {
                            return $fail(__('The items are not in the list.'));
                        }

                        if (! count($items)) {
                            return $fail(__('The item is not exist.'));
                        }

                        $postId = 0;
                        $advertising = Advertising::findByField('id', $id);
                        if ($advertising) {
                            $postId = $advertising->post_id;
                        }
                        
                        foreach ($items as $itemId) {
                            $item = PostItem::findByField('id', $itemId);
                            if (! $item || ! $item->canStore($viewer->id, $subjectType, $postId)) {
                                return $fail(__('The item is not exist.'));
                            }
                        }

                        if (setting('feature.post_photo_max') && $type == 'photo' && count($items) > setting('feature.post_photo_max')) {
                            return $fail(__('You can only share :number photos at a time.',['number' => setting('feature.post_photo_max')]));
                        }

                        if (in_array($type, ['link', 'video']) && count($items) > 1) {  
                            return $fail(__('The item is not exist.'));
                        }

                        if ($type == 'video') {
                            $item = $items[0];
                            $item = PostItem::findByField('id', $itemId);
                            if ($item->has_queue) {
                                return $fail(__('The item is not exist.'));
                            }
                        }
                    },
                ];
                    
                $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));
                break;
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $result = CountryValidate::runFormRequest($this->request->all());
            
            if ($result->fails()) {
                foreach ($result->getMessageBag()->getMessages() as $key => $messages) {
                    $validator->errors()->add($key, $messages[0]);
                }

                return;
            }
        });

        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                $data = $validator->getData();
                if ($data['age_from'] && $data['age_to']) {
                    if ($data['age_to'] < $data['age_from']) {
                        $validator->errors()->add('end', __('The age to must be greater than the age from.'));
                    }
                }

                $this->checkPermissionHaveValue('post.character_max', strlen($data['content']), $user);

                $advertising = Advertising::findByField('id', $data['id']);
                $post = $advertising->getPost();
                if ($post->is_paid) {
                    if (! in_array($data['type'], ['photo', 'video'])) {
                        throw new MessageHttpException(__('Please upload media file for this ads.'));
                    }
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.'),
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 255 characters.'),
            'content.required' => __('The content is required.'),
            'photos.required' => __('Photos is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => getMaxTextSql(setting('feature.post_character_max'))]),
            'age_from.required' => __('The age from is required.'),
            'age_to.required' => __('The age to is required.'),
            'age_from.min' => __('The age from must be greater than 0.'),
            'age_to.min' => __('The age to must be greater than 0.'),
        ];
    }
}
