<?php

namespace Packages\ShaunSocial\UserVerify\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserVerify\Http\Requests\StoreRequestValidate;
use Packages\ShaunSocial\UserVerify\Http\Requests\UploadFileValidate;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\UserVerify\Http\Requests\DeleteFileValidate;
use Packages\ShaunSocial\UserVerify\Repositories\Api\UserVerifyRepository;

class UserVerifyController extends ApiController
{
    protected $userVerifyRepository;

    public function __construct(UserVerifyRepository $userVerifyRepository)
    {
        if (! setting('user_verify.enable') && !setting('feature.identity_verify')) {
            return $this->errorMessageRespone('This function has been disabled.');
        }
        $routerName = Route::getCurrentRoute()->getName();
        if ($routerName == 'api.user_verify_store_request') {
            $this->middleware('has.permission:user_verify.send_request');
        }
        $this->userVerifyRepository = $userVerifyRepository;
        parent::__construct();
    }

    public function get_files(Request $request)
    {
        $result = $this->userVerifyRepository->get_files($request->user());

        return $this->successResponse($result);
    }

    public function upload_file(UploadFileValidate $request)
    {
        $result = $this->userVerifyRepository->upload_file($request->file('file'), $request->user()->id);

        return $this->successResponse($result); 
    }


    public function delete_file(DeleteFileValidate $request)
    {
        $this->userVerifyRepository->delete_file($request->id);

        return $this->successResponse(); 
    }

    public function store_request(StoreRequestValidate $request)
    {
        $this->userVerifyRepository->store_request($request->get('files'),$request->user());

        return $this->successResponse();
    }
}