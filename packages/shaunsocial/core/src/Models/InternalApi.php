<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class InternalApi extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'key',
    ];

    protected $fillable = [
        'key',
        'name'
    ];
}
