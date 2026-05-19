<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class CodeVerify extends Model
{
    use Prunable, HasCacheQueryFields;

    protected $fillable = [
        'user_id',
        'type',
        'email',
        'code',
    ];

    protected $cacheQueryFields = [
        'code'
    ];

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
