<?php

namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Illuminate\Support\Facades\Log;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Wallet\Models\WalletOrder;
use Packages\ShaunSocial\Wallet\Models\WalletPackage;
use Readdle\AppStoreServerAPI\AppStoreServerAPI;
use Readdle\AppStoreServerAPI\Environment;
use Readdle\AppStoreServerAPI\Exception\AppStoreServerAPIException;

class StoreInAppPurchaseAppleValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'data' => [
                'required',
                function ($attribute, $data, $fail) {
                    $normalized = [];
                    if (is_string($data)) {
                        if (! validateJson($data)) {
                            return $fail(__('The data must be json format.'));
                        }
                        $data = json_decode($data, true);
                    }

                    if (! is_array($data)) {
                        return $fail(__('The data must be json format.'));
                    }

                    if (! count($data)) {
                        return $fail(__('The data must be json format.'));
                    }

                    foreach ($data as $item) {
                        if (empty($item['verificationData']['serverVerificationData'])) {
                            return $fail(__('The data must be json format.'));
                        }

                        //Log::info('Apple IAP payload item', ['item' => $item]);

                        try {
                            $issuerId = env('APPLE_ISSUER_ID');
                            $keyId = env('APPLE_KEY_ID');
                            $privateKey = env('APPLE_PRIVATE_KEY');
                            $bundleId = env('APPLE_BUNDLE_ID');
                            $environment = env('APPLE_ENV', Environment::SANDBOX);

                            Log::info('Apple IAP server api config', [
                                'issuer_id' => $issuerId,
                                'key_id' => $keyId,
                                'bundle_id' => $bundleId,
                                'environment' => $environment,
                                'private_key_is_path' => $privateKey ? file_exists($privateKey) : false,
                            ]);

                            if (! $issuerId || ! $keyId || ! $privateKey) {
                                return $fail(__('Apple server API credentials are missing.'));
                            }

                            if (! in_array($environment, [Environment::SANDBOX, Environment::PRODUCTION], true)) {
                                return $fail(__('Apple server API environment is invalid.'));
                            }

                            if (file_exists($privateKey)) {
                                $privateKey = file_get_contents($privateKey);
                            }

                            $transactionId = $item['transactionId'] ?? $item['purchaseID'] ?? null;
                            if (isset($item['verificationData']['localVerificationData']) && validateJson($item['verificationData']['localVerificationData'])) {
                                $localData = json_decode($item['verificationData']['localVerificationData'], true);
                                $transactionId = $transactionId ?: ($localData['transactionId'] ?? null);
                            }

                            if (! $transactionId) {
                                return $fail(__('The data must be json format.'));
                            }

                            Log::info('Apple IAP transaction lookup', ['transaction_id' => $transactionId]);

                            $api = new AppStoreServerAPI(
                                $environment,
                                $issuerId,
                                $bundleId,
                                $keyId,
                                $privateKey
                            );
                        } catch (\Exception $e) {
                            Log::error('Apple IAP api init failed', ['error' => $e->getMessage()]);
                            return $fail($e->getMessage());
                        }

                        try {
                            $transactionInfo = $api->getTransactionInfo($transactionId)->getTransactionInfo();
                        } catch (AppStoreServerAPIException $e) {
                            Log::error('Apple IAP transaction lookup failed', [
                                'transaction_id' => $transactionId,
                                'error' => $e->getMessage(),
                            ]);
                            return $fail($e->getMessage());
                        }

                        Log::info('Apple IAP selected transaction', [
                            'product_id' => $transactionInfo->getProductId(),
                            'transaction_id' => $transactionInfo->getTransactionId(),
                            'environment' => $transactionInfo->getEnvironment(),
                        ]);

                        if ($transactionInfo->getBundleId() !== $bundleId) {
                            return $fail(__('The apple bundle id is not correct.'));
                        }

                        $productId = $transactionInfo->getProductId();
                        $transactionId = (string) $transactionInfo->getTransactionId();
                        if (! $productId || ! $transactionId) {
                            return $fail(__('The data must be json format.'));
                        }

                        $package = WalletPackage::findByField('apple_price_id', $productId);
                        if (! $package || $package->is_delete) {
                            return $fail(__('The package not found.'));
                        }

                        $item['_validated_product_id'] = $productId;
                        $item['_validated_transaction_id'] = $transactionId;
                        $item['_validated_receipt'] = $transactionInfo->jsonSerialize();
                        $normalized[] = $item;
                    }

                    $this->merge(['data' => $normalized]);
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