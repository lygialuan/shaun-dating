<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Enum\PostPaidType;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Validation\AmountValidation;

class StoreEditPostValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        $rules = [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $postId, $fail) use ($viewer) {
                    $post = Post::findByField('id', $postId);
                    
                    if (! $post || ! $post->canChangePaidContent($viewer->id)) {
                        return $fail(__('The post is not found.')); 
                    }
                },
            ],
            'thumb_delete' => [
                'boolean'
            ]
        ];

        $rules['is_paid'] = [
            'boolean'
        ];
        
        $rules['paid_type'] = [
            'nullable',
            Rule::in(PostPaidType::values())
        ];

        if ($this->input('paid_type') == PostPaidType::PAYPERVIEW->value) {
            $rules['content_amount'] = ['required', new AmountValidation(), 'numeric', 'min:1'];
        } else {
            $this->merge([
                'content_amount' => 0,
            ]);
        }

        if ($this->input('is_paid')) {
            $rules['paid_type'] = [
                'required',
                Rule::in(PostPaidType::values())
            ];
            $rules['thumb_file_id'] = [
                'nullable',
                function ($attribute, $thumbFileId, $fail) use ($viewer) {
                    if ($thumbFileId) {
                        $thumb = StorageFile::findByField('id', $thumbFileId);
                        if (! $thumb || ! $thumb->canStore($viewer->id, 'post_review')) {
                            return $fail(__('The thumb is not found.'));
                        }
                    }
                },
            ];
        }

        return $rules;
    }

    public function prepareForValidation()
    {
        $this->mergeIfMissing([
            'is_paid' => false
        ]);
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'thumb_file_id.required' => __('The thumbnail is required.'),
            'content_amount.required' => __('The amount is required.'),
            'content_amount.min' => __('The amount must be at least 1.'),
        ];
    }
}
