<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\User;

class UserPageNotificationCron extends Model
{
    protected $fillable = [
        'user_page_id',
        'type',
        'current',
    ];

    public function getPage()
    {
        return User::findByField('id', $this->user_page_id);
    }

    public static function add($pageId, $type)
    {
        $lock = Cache::lock('user_page_notification_cron'.$pageId, config('shaun_user_page.notification_lock'));
 
        if ($lock->get()) {
            $cron = self::where('user_page_id', $pageId)->where('type', $type)->first();
            if (! $cron) {
                self::create([
                    'user_page_id' => $pageId,
                    'type' => $type
                ]);
            }
        }
    }
}
