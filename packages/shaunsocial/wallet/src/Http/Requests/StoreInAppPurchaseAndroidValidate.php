<?php

namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Wallet\Models\WalletOrder;
use Packages\ShaunSocial\Wallet\Models\WalletPackage;

class StoreInAppPurchaseAndroidValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'data' => [
                'required',
                'string',
                function ($attribute, $data, $fail) {
                    if (! validateJson($data)) {
                        return $fail(__('The data must be json format.'));
                    }
                    $data = json_decode($data, true);
                    $data = $data[0];

                    if (empty($data['verificationData']['localVerificationData'])) {
                        return $fail(__('The data must be json format.'));
                    }

                    if (! validateJson($data['verificationData']['localVerificationData'])) {
                        return $fail(__('The data must be json format.'));
                    }

                    $data = json_decode($data['verificationData']['localVerificationData'], true);

                    $listValidate = ['purchaseToken', 'productId', 'orderId'];
                    foreach ($listValidate as $key) {
                        if (empty($data[$key])) {
                            return $fail(__('The data must be json format.'));
                        }
                    }
                    $googleClient = new \Google_Client();
                    $googleClient->setScopes([\Google\Service\AndroidPublisher::ANDROIDPUBLISHER]);        
                    $googleClient->setAuthConfig(base_path('server.json'));

                    $googleAndroidPublisher = new \Google\Service\AndroidPublisher($googleClient);
                    $validator = new \ReceiptValidator\GooglePlay\Validator($googleAndroidPublisher);

                    try {
                        $response = $validator->setPackageName(setting('mobile.android_package_name'))
                            ->setProductId($data['productId'])
                            ->setPurchaseToken($data['purchaseToken'])
                            ->validatePurchase();

                    } catch (\Exception $e){
                        return $fail($e->getMessage());
                    }

                    $order = WalletOrder::findByField('gateway_transaction_id', $data['orderId']);
                    if ($order) {
                        return $fail('The order already exists.');
                    }

                    $package = WalletPackage::findByField('google_price_id', $data['productId']);
                    if (! $package || $package->is_delete) {
                        return $fail('The package not found.');
                    }
                },
            ]
    ];
    }

    public function messages()
    {
        return [
            'data.required' => __('The gateway id is required.'),
        ];
    }
}
