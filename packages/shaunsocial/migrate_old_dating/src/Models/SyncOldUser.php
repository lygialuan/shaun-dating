<?php

namespace Packages\ShaunSocial\MigrateOldDating\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class SyncOldUser extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'status',
        'last_id',
        'total',
        'database_host',
        'port',
        'database_name',
        'user_name',
        'password',
    ];
}
