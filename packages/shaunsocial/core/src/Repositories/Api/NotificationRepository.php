<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Notification\NotificationResource;
use Packages\ShaunSocial\Core\Models\UserDailyEmail;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Traits\Utility;

class NotificationRepository
{
    use HasUserList, Utility;

    public function get($viewer, $page, $clear)
    {
        if ($viewer->notify_count && $clear) {
            $dailyEmail = UserDailyEmail::findByField('user_id', $viewer->id);
            if ($dailyEmail) {
                $dailyEmail->delete();
            }
            $viewer->update(['notify_count' => 0]);
            UserNotification::where('user_id', $viewer->id)->where('is_viewed', false)->update([
                'is_viewed' => true
            ]);
        }

        $builder = UserNotification::where('user_id', $viewer->id)->select(DB::raw('max(id) as notify_id'))->groupBy('hash')->orderBy('notify_id', 'DESC');
        $results = UserNotification::getCachePagination('user_'.$viewer->id, $builder, $page);
        $resultsNextPage = UserNotification::getCachePagination('user_'.$viewer->id, $builder, $page + 1);
        $notifications = $this->filterNotification($results, $viewer->id);

        return [
            'items' => NotificationResource::collection($this->filterUserList($notifications, $viewer, 'from_id')),
            'has_next_page' => count($resultsNextPage) ? true : false
        ];
    }
    
    public function store_enable($data, $viewerId)
    {
        $subject = findByTypeId($data['subject_type'], $data['subject_id']);

        switch ($data['action']) {
            case 'remove':
                $subject->addNotificationStop($viewerId);
                break;
            case 'add':
                $stop = $subject->getNotificationStop($viewerId);
                $stop->delete();
                break;
        }
    }

    public function store_seen($id, $viewer)
    {
        $notification = UserNotification::findByField('id', $id);
        if (! $notification->is_seen) {
            $notification->update(['is_seen' => true, 'is_viewed' => true]);
            UserNotification::where('hash', $notification->hash)->update(['is_seen' => true, 'is_viewed' => true]);
            $count = $viewer->notify_count - 1;
            if ($count < 0) {
                $count = 0;
            }
            $viewer->update(['notify_count' => $count]);
        }
    }

    public function mark_all_as_read($viewerId)
    {
        Cache::set('user_notification_mark_all_as_read_'. $viewerId, now()->timestamp, config('shaun_core.cache.time.model_query'));
        UserNotification::where('user_id', $viewerId)->where('is_seen', false)->update([
            'is_seen' => true,
            'is_viewed' => true
        ]);
    }
}
