<?php


namespace Packages\ShaunSocial\Core\Services;

use Illuminate\Support\Collection;
use Packages\ShaunSocial\Core\Jobs\SendNotificationJob;
use Packages\ShaunSocial\Core\Models\UserDailyEmail;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\UserPage\Models\UserPageNotificationCron;

class Notification
{
    protected $users = [];

    public function send($users, $from, $notifyClass, $subject = null, $data = [], $package = 'shaun_core', $hasGroup = true, $unique = true, $forceSend = false)
    {
        if (! $users instanceof Collection && ! is_array($users)) {
            $users = [$users];
        }

        foreach ($users as $user) {             
            if (! $user) {
                continue;
            }

            if (! $user->id) {
                continue;
            }
            
            if ($unique && in_array($user->id, $this->users)) {
                continue;
            }

            if (! $user->enable_notify) {
                continue;
            }

            if ($from->checkBlock($user->id)) {
                continue;
            }

            $hashString = $user->id.'_'.$notifyClass;
            if ($subject) {
                if (! $forceSend && method_exists($subject, 'checkNotification') && ! $subject->checkNotification($user->id)) {
                    continue;
                }
                
                $hashString .= '_'.$subject->getSubjectType().'_'.$subject->id;
            }

            if (!$hasGroup) {
                $hashString .= '_'.time();
            } else {
                $hashString .= '_'.date('m');
            }
            $hashString = md5($hashString);

            $sameNotification = UserNotification::getSameHash($hashString);
            if (!$sameNotification || $sameNotification->is_viewed) {
                $user->increment('notify_count');                
            }
            
            if ($user->daily_email_enable && $user->is_active && $user->has_active) {                
                $dailyEmail = UserDailyEmail::findByField('user_id', $user->id);
                if (! $dailyEmail) {
                    UserDailyEmail::create([
                        'user_id' =>  $user->id,
                    ]);
                }
            }

            if (!empty($data['params']) && !is_string($data['params'])) {
                $data['params'] = json_encode($data['params']);
            }

            $notification = UserNotification::create([
                'user_id' => $user->id,
                'from_id' => $from->id,
                'class' => $notifyClass,
                'subject_type' => $subject ? $subject->getSubjectType() : '',
                'subject_id' => $subject ? $subject->id : 0,
                'hash' => $hashString,
                'package' => $package
            ] + $data);

            //check page
            if ($user->isPage()) {
                UserPageNotificationCron::add($user->id, 'page_notify');
            }

            if (config('shaun_core.core.queue')) {
                dispatch((new SendNotificationJob($notification))->onQueue(config('shaun_core.queue.notification')));
            } else {
                SendNotificationJob::dispatchSync($notification);
            }

            if ($unique) {
                $this->users[] = $user->id;
            }
        }
    }
}
