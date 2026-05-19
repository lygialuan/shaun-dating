<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserDailyEmail extends Model
{
    use HasCacheQueryFields, HasUser;
    protected $cacheQueryFields = [
        'user_id',
    ];

    protected $fillable = [
        'user_id'
    ];
}
