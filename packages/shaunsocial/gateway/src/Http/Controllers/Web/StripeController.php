<?php

namespace Packages\ShaunSocial\Gateway\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Gateway\Models\Gateway;

class StripeController extends Controller
{
    public function ipn(Request $request)
    {
        $stripe = Gateway::findByField('key', 'stripe');
        $stripe->getClass()->ipn();
    }
}
