<?php

namespace Packages\ShaunSocial\Chatbot\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Chatbot\Http\Requests\StoreMessageValidate;
use Packages\ShaunSocial\Chatbot\Repositories\Api\ChatbotRepository;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;


class ChatbotController extends ApiController
{
    protected ChatbotRepository $repository;

    public function __construct(ChatbotRepository $repository)
    {
        if (! setting('ai_features.chatbot_enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }
        $current = Route::getCurrentRoute();
        switch ($current->getActionMethod()) {
            case 'send_message':
                $this->middleware('has.permission:chatbot.allow');
                break;
        }
        $this->repository = $repository;
    }

    /**
     * Send message to chatbot
     */
    public function send_message(StoreMessageValidate $request)
    {
        $result = $this->repository->send_message($request->user(), $request->message);

        if ($result['status']) {
            return $this->successResponse($result);
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    /**
     * Get conversation history from database with pagination
     */
    public function get_history(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->repository->get_history($request->user(), $page);
        return $this->successResponse($result);
    }

    /**
     * Get available providers
     */
    public function get_provider()
    {
        return $this->successResponse($this->repository->get_provider());
    }

    /**
     * Clear all chatbot history for the current user
     */
    public function clear_history(Request $request)
    {
        $result = $this->repository->clear_history($request->user());

        return $this->successResponse($result);
    }
}
