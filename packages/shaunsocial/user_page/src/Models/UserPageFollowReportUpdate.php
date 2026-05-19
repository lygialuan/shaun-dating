<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserPageFollowReportUpdate extends Model
{
    use HasUser;
    
    protected $fillable = [
        'user_id',
        'current'
    ];

    public static function add($userId)
    {
        self::firstOrCreate([
            'user_id' => $userId
        ]);
    }
}
