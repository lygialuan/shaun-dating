<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Repositories\Api\HashtagRepository;

class HashtagSuggestWidget extends BaseWidget
{
    protected $hashtagRepository;

    public function __construct(HashtagRepository $hashtagRepository)
    {
        $this->hashtagRepository = $hashtagRepository;
    }

    public function getData($request, $params = [])
    {
        $viewer = $request->user();
        $result = $this->hashtagRepository->suggest($viewer, $params['item_number']);
        return count($result) ? $result->values()->toArray($request) : false;
    }
}
