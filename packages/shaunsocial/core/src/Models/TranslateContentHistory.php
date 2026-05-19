<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class TranslateContentHistory extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'hash'
    ];

    protected $fillable = [
        'hash',
        'result'
    ];
}
