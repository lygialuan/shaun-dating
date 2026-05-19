<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Notification\StoreEnableNotificationValidate;
use Packages\ShaunSocial\Core\Http\Requests\Notification\StoreNotificationSeenValidate;
use Packages\ShaunSocial\Core\Repositories\Api\NotificationRepository;

class NotificationController extends ApiController
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
        parent::__construct();
    }

    public function get(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        $clear = $request->input('clear', false);

        $result = $this->notificationRepository->get($request->user(), $page, $clear);

        return $this->successResponse($result);
    }

    public function store_enable(StoreEnableNotificationValidate $request)
    {
        $this->notificationRepository->store_enable($request->all(), $request->user()->id);
    
        return $this->successResponse();
    }

    public function store_seen(StoreNotificationSeenValidate $request)
    {
        $this->notificationRepository->store_seen($request->id, $request->user());

        return $this->successResponse();
    }

    public function mark_all_as_read(Request $request)
    {
        $this->notificationRepository->mark_all_as_read($request->user()->id);

        return $this->successResponse();
    }
}
