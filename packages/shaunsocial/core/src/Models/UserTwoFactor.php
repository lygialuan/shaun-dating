<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserTwoFactor extends Model
{
    use HasCacheQueryFields, HasUser;

    protected $cacheQueryFields = [
        'user_id',
    ];
    
    protected $fillable = [
        'user_id',
        'provider_id',
        'params',
        'is_active'
    ];

    public static function getByUser($userId,$active = false)
    {
        $userTwoFactor = self::findByField('user_id', $userId);
        if ($active) {
            if ($userTwoFactor && $userTwoFactor->is_active) {
                return $userTwoFactor;
            }
        } else {
            return $userTwoFactor;
        }

        return null;
    }

    public function getParams()
    {
        if ($this->params) {
            return json_decode($this->params, true);
        }

        return [];
    }

    public function getProvider()
    {
        return TwoFactorProvider::findByField('id', $this->provider_id);
    }
}
