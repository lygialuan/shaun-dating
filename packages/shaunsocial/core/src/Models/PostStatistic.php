<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PostStatistic extends Model
{
    protected $fillable = [
        'post_id',
        'type',
    ];

    public static function add($viewer, $type, $postId, $hasLock = true)
    {
        if ($hasLock) {
            $key = 'post_statistic_lock_'.$postId.$type;
            if ($viewer) {
                $key.=$viewer->id;
            } else {
                $key.=request()->ip();
            }
            $lock = Cache::lock($key, config('shaun_core.core.statistic_lock'));
            if (! $lock->get()) {
                return;
            }
        }
        if ($type == 'post_reach') {
            self::create([
                'post_id' => $postId,
                'type' => $type
            ]);
        }
    }
}
