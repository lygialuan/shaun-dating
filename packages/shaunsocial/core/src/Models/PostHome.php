<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Traits\HasSource;

class PostHome extends Model
{
    use Prunable, HasSource;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'hashtags',
        'parent_id',
        'post_id',
        'like_count',
        'comment_count',
        'user_privacy',
        'total_count',
        'show',
        'is_paid'
    ];
    
    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($post) {
            if (!$post->parent_id) {
                self::where('parent_id', $post->id)->get()->each(function ($post) {
                    $post->delete();
                });
            }
        });
        
        static::saving(function ($post) {
            if ($post->like_count != $post->getOriginal('like_count') || ($post->comment_count != $post->getOriginal('comment_count'))) {
                $post->total_count = $post->like_count + $post->comment_count;
            }
        });
    }

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(setting('feature.post_home_delete_day')))->limit(setting('feature.item_per_page'));
    }

    public static function updatePrivacy($userId, $privacy)
    {
        self::where('user_id', $userId)->update([
            'user_privacy' => $privacy
        ]);
    }
}
