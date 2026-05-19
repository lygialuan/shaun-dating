<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class HashtagTrending extends Model
{
    use Prunable;

    protected $fillable = [
        'hashtag_id',
        'name',
        'is_active',
        'post_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(setting('feature.hashtag_trending_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
