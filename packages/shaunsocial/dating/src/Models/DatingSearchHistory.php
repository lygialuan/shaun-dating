<?php

namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class DatingSearchHistory extends Model
{
    use HasCacheQueryFields, HasUser;

    protected $cacheQueryFields = [
        'id',
        'user_id'
    ];

    protected $fillable = [
        'query',
        'user_id',
    ];
}
