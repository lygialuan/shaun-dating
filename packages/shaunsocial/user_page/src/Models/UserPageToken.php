<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class UserPageToken extends Model
{
    use HasCacheQueryFields;
    
    protected $cacheQueryFields = [
        'token'
    ];

    protected $fillable = [
        'user_id',
        'token',
        'user_page_id'
    ];
}
