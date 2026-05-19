<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Search;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\SearchHistory;

class DeleteSearchHistoryValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $searchHistory = SearchHistory::findByField('id', $id);
                    if (! $searchHistory || ! $searchHistory->isOwner($viewer->id)) {
                        return $fail(__('Search history is not found'));
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required'),
        ];
    }
}
