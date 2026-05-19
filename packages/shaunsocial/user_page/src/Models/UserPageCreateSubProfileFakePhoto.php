<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class UserPageCreateSubProfileFakePhoto extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'user_id'
    ];

    protected $fillable = [
        'gender',
        'photo',
        'user_id'
    ];
}
