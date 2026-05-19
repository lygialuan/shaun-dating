<?php

namespace Packages\ShaunSocial\AiChatProfiles\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\AiChatProfiles\Repositories\Api\AiSuggestionRepository;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;

class AiSuggestionController extends ApiController
{
    protected $aiSuggestionRepository;

    public function __construct(AiSuggestionRepository $aiSuggestionRepository)
    {
        if (!setting('ai_chat_profiles.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }

        $this->aiSuggestionRepository = $aiSuggestionRepository;
        
        parent::__construct();
    }

    public function suggestion(Request $request)
    {
        $result = $this->aiSuggestionRepository->suggestion($request->user()->id, $request->id);

        return $this->successResponse($result);
    }
}

