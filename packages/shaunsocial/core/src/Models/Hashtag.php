<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasCacheSearch;

class Hashtag extends Model
{
    use HasCacheQueryFields, HasCacheSearch;

    protected $cacheQueryFields = [
        'id',
        'name',
    ];

    protected $fillable = [
        'name',
        'post_count',
        'follow_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $cacheSearchFields = [
        'name' => [
            'is_active',
            'post_count'
        ],
    ];

    protected static function booted()
    {
        parent::booted();
        
        static::creating(function ($hashtag) {
            if ($hashtag->is_active === null) {
                $hashtag->is_active = setting('feature.hashtag_auto_approve') ? true : false;
            }
        });
    }
}
