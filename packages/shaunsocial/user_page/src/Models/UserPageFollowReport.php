<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;

class UserPageFollowReport extends Model
{
    protected $fillable = [
        'user_page_id',
        'user_id',
        'gender_id',
        'birthday'
    ];

    public static function getByUserIdAndPageId($userId, $pageId)
    {
        return self::where('user_id', $userId)->where('user_page_id', $pageId)->first();
    }
}
