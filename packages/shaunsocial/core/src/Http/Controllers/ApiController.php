<?php


namespace Packages\ShaunSocial\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Traits\ApiResponser;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponser;

    public function getWhitelistForceLogin()
    {
        return [];
    }

    public function __construct()
    {                
        $whiteList = $this->getWhitelistForceLogin();
        $current = Route::getCurrentRoute();
        if (! in_array($current->getActionMethod(), $whiteList)) {
            if (setting('feature.force_login')) {            
                if (isset($current->action['excluded_middleware'])) {
                    $index = array_search('auth:sanctum', $current->action['excluded_middleware']);
    
                    if ($index !== false) {                 
                        unset($current->action['excluded_middleware'][$index]);   
                    }
                }
                $this->middleware('auth:sanctum');
            }
        }
    }

    public function wantsJson()
    {
        return true;
    }
}
