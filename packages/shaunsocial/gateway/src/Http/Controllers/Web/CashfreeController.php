<?php

namespace Packages\ShaunSocial\Gateway\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Gateway\Models\Gateway;

class CashfreeController extends Controller
{
    public function ipn(Request $request)
    {
        $gateway = Gateway::findByField('key', 'cashfree');
        $gateway->getClass()->ipn();
    }
}
