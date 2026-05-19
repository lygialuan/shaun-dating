<?php

namespace Packages\ShaunSocial\GatewayRecurring\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\ShaunSocial\GatewayRecurring\Models\GatewayRecurring;

class CCBillController extends Controller
{
    public function ipn(Request $request)
    {
        $gatewayRecurring = GatewayRecurring::findByField('key', 'ccbill');
        $gatewayRecurring->getClass()->ipn();
    }
}
