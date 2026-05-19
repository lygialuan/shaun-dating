<?php

namespace Packages\ShaunSocial\Gateway\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Gateway\Models\Gateway;
use Packages\ShaunSocial\Wallet\Models\WalletOrder;
use Razorpay\Api\Api;

class RazorPayController extends Controller
{
    public function ipn(Request $request)
    {
        $gateway = Gateway::findByField('key', 'razorpay');
        $gateway->getClass()->ipn();
    }
    public function showRazorpayPage($order_id)
    {
        $gateway = Gateway::findByField('key', 'razorpay');
        $config = $gateway->getConfig();


        $api = new Api($config['key_id'], $config['key_secret']);


        $razorOrder = $api->order->fetch($order_id);


        $order = WalletOrder::findByField('id', (int)$razorOrder->receipt);

        $user = $order->getUser();

        return view('shaun_core::razorpay_checkout', [
            'key' => $config['key_id'],
            'order_id' => $order_id,
            'amount' => $razorOrder['amount'],
            'currency' => $razorOrder['currency'],
            'description' => $order->description ?? '',
            'name' => $user->name ?? '',
            'email' => $user->email ?? '',
            'url_successful' => url('/wallet'),
            'url_cancel' => url('/wallet'),
        ]);
    }
}
