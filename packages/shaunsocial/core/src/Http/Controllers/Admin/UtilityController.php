<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;

class UtilityController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    public function user_suggest(Request $request)
    {
        $result = $this->userRepository->search_name($request->text, $request->user());

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }
}
