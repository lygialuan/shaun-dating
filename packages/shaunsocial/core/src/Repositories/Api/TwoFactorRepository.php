<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\RateLimiter;
use Packages\ShaunSocial\Core\Http\Resources\TwoFactor\UserTwoFactorResource;
use Packages\ShaunSocial\Core\Models\CodeVerify;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Traits\Utility;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorRepository
{
    use Utility;

    public function get_current($viewer)
    {
        $userTwoFactor = UserTwoFactor::getByUser($viewer->id, true);
        if ($userTwoFactor) {
            return new UserTwoFactorResource($userTwoFactor);
        }
        return null;
    }

    public function remove($viewer)
    {
        $userTwoFactor = UserTwoFactor::getByUser($viewer->id, true);
        $userTwoFactor->delete();
    }
    
    public function send_setup_email($email, $viewer)
    {
        $key = 'send_email_'.$viewer->id.$email;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return [
                'status' => false,
                'message' => __('Please wait for a minute to try again.')
            ];
        }

        $code = $this->createCodeVerify($viewer->id, 'two_factor_setup_send_email', $email);
        Mail::send('two_factory_send_code', $email, ['code' => $code]);
        RateLimiter::increment($key, config('shaun_core.core.send_email_attempt_limit'));

        return ['status' => true];
    }

    public function verify_setup_email($email, $viewer)
    {
        $userTwoFactor = UserTwoFactor::getByUser($viewer->id);
        if ($userTwoFactor) {
            $userTwoFactor->delete();
        }

        $provider = TwoFactorProvider::getByType('mail');

        $userTwoFactor = UserTwoFactor::create([
            'user_id' => $viewer->id,
            'provider_id' => $provider->id,
            'is_active' => true,
            'params' => json_encode([
                'email' => $email
            ])
        ]);

        return new UserTwoFactorResource($userTwoFactor);
    }

    public function send_setup_phone($phoneNumber, $viewer)
    {
        $code = $this->createCodeVerify($viewer->id, 'two_factor_setup_send_sms', $phoneNumber);
        
        return $this->sendPhoneNumber($viewer->id, $phoneNumber, __('Code is').': '. $code);
    }

    public function verify_setup_sms($phoneNumber, $viewer)
    {
        $userTwoFactor = UserTwoFactor::getByUser($viewer->id);
        if ($userTwoFactor) {
            $userTwoFactor->delete();
        }

        $provider = TwoFactorProvider::getByType('sms');

        $userTwoFactor = UserTwoFactor::create([
            'user_id' => $viewer->id,
            'provider_id' => $provider->id,
            'is_active' => true,
            'params' => json_encode([
                'phone_number' => $phoneNumber
            ])
        ]);

        return new UserTwoFactorResource($userTwoFactor);
    }

    public function get_code_app($viewer)
    {
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            setting('site.title'),
            setting('site.email'),
            $secretKey
        );

        $provider = TwoFactorProvider::getByType('auth_app');

        $userTwoFactor = UserTwoFactor::findByField('user_id', $viewer->id);
        if ($userTwoFactor) {
            $userTwoFactor->update([
                'provider_id' => $provider->id,
                'is_active' => false,
                'params' => json_encode([
                    'secret_key' => $secretKey
                ])
            ]);
        } else {
            UserTwoFactor::create([
                'user_id' => $viewer->id,
                'provider_id' => $provider->id,
                'is_active' => false,
                'params' => json_encode([
                    'secret_key' => $secretKey
                ])
            ]);
        }
        $writer = new Writer(
            new GDLibRenderer(
                400
            )
        );
        
        return [
            'qr_code' =>  base64_encode($writer->writeString($qrCodeUrl))
        ];
    }

    public function verify_code_app($viewer)
    {
        $userTwoFactor = UserTwoFactor::findByField('user_id', $viewer->id);
        $userTwoFactor->update([
            'is_active' => true,
        ]);

        return new UserTwoFactorResource($userTwoFactor);
    }

    public function send_login_code($code)
    {
        $userVerify = CodeVerify::findByField('code', $code);
        $userTwoFactor = UserTwoFactor::findByField('user_id', $userVerify->user_id);
        $provider = $userTwoFactor->getProvider();
        $params = $userTwoFactor->getParams();
        
        switch ($provider->type) {
            case 'sms':
                return $this->send_setup_phone($params['phone_number'], $userTwoFactor->getUser());
                break;
            case 'mail':
                return $this->send_setup_email($params['email'],$userTwoFactor->getUser());
                break;
        }

        return ['status' => true];
    }

    public function get_login_current($code)
    {
        $userVerify = CodeVerify::findByField('code', $code);
        $userTwoFactor = UserTwoFactor::findByField('user_id', $userVerify->user_id);
        return new UserTwoFactorResource($userTwoFactor);
    }
}
