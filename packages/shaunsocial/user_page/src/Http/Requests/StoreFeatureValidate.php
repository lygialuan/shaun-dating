<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\UserPage\Models\UserPageFeaturePackage;

class StoreFeatureValidate extends PageValidate
{
    public function authorize()
    {
        return setting('shaun_user_page.feature_enable');
    }

    public function rules()
    {
        return [
            'package_id' => [
                'required',
                function ($attribute, $packageId, $fail) {
                    $viewer = $this->user();
                    if ($viewer->isPageFeature()) {
                        return $fail(__("You've already featured."));
                    }

                    $package = UserPageFeaturePackage::findByField('id', $packageId);

                    if (! $package || $package->isDeleted()) {
                        return $fail(__('The feature package is not found.'));
                    }

                    if ($viewer->getCurrentBalance() < $package->amount) {
                        return $fail(__("You don't have enough balance"));
                    }
                },
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'package_id.required' => __('The package id is required.'),
            'password.required' => __('The password is required.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
