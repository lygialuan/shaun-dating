<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class OpenidProviderUser extends Model
{
    protected $fillable = [
        'provider_id',
        'user_id',
        'access_token',
        'provider_uid'
    ];

    static public function checkProviderIdentity($providerId, $uid)
    {
        return self::where('provider_id', $providerId)->where('provider_uid', $uid)->first();
    }
}
