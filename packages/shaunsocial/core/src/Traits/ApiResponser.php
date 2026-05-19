<?php


namespace Packages\ShaunSocial\Core\Traits;

trait ApiResponser
{
    protected function successResponse($data = 'Ok', $code = 200)
    {
        return response()->json([
            'data' => $data,
        ], $code);
    }

    protected function errorResponse($message = null, $detail = [], $codeError = 'error', $codeRespone = 400)
    {
        return response()->json([
            'error' => [
                'code' => $codeError,
                'message' => $message,
                'detail' => $detail,
                'headerCode' => $codeRespone
            ],
        ], $codeRespone);
    }

    protected function errorValidateRespone($detail = [])
    {
        return $this->errorResponse('', $detail, 'error_validate', 400);
    }

    protected function errorMessageRespone($message)
    {
        return $this->errorResponse($message, [], 'error_message', 400);
    }

    protected function messageWithCodeResponse($message, $codeError, $coderesponse)
    {
        return $this->errorResponse($message, [], $codeError, $coderesponse);
    }

    protected function errorNotFound($message)
    {
        return $this->errorResponse($message, [], 'not_found', 404);
    }

    protected function accessTokenMessageRespone($message, $token)
    {
        $response = $this->successResponse($message);

        if (request()->headers->get('SupportCookie')) {
            $response = setAppCookie($response, 'access_token', $token, config('shaun_core.core.time_coookie_login'));
        }

        return $response;
    }
}
