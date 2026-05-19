<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Invite\GetInviteValidate;
use Packages\ShaunSocial\Core\Http\Requests\Invite\StoreCsvInviteValidate;
use Packages\ShaunSocial\Core\Http\Requests\Invite\StoreInviteValidate;
use Packages\ShaunSocial\Core\Repositories\Api\InviteRepository;

class InviteController extends ApiController
{
    protected $inviteRepository;

    public function __construct(InviteRepository $inviteRepository)
    {
        $this->inviteRepository = $inviteRepository;
        parent::__construct();
    }

    public function info(Request $request)
    {
        $result = $this->inviteRepository->info($request->user());

        return $this->successResponse($result);
    }

    public function get(GetInviteValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->inviteRepository->get($request->query('query'), $request->query('type'), $page, $request->user());

        return $this->successResponse($result);
    }

    public function store(StoreInviteValidate $request)
    {
        $request->mergeIfMissing([
            'message' => ''
        ]);

        $result = $this->inviteRepository->store($request->only([
            'emails',
            'message'
        ]), $request->user());

        return $this->successResponse();
    }

    public function store_csv(StoreCsvInviteValidate $request)
    {
        $this->inviteRepository->store_csv($request->emails ,$request->user());

        return $this->successResponse();
    }
}
