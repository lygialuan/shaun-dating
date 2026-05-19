<?php


namespace Packages\ShaunSocial\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Traits\Utility;

class SendNotificationJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Utility;

    protected $notification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notification = $this->notification;
        if (config('shaun_core.core.queue') && $notification->getClassNotification()->hasGroup()) {
            $notification = UserNotification::where('hash', $notification->hash)->where('user_id', $notification->user_id)->orderBy('id', 'DESC')->first();
        }
        if (! $notification->checkExists()) {
            return;
        }

        $user = $notification->getUser();
        $from = $notification->getFrom();
        if (! $user || ! $from) {
            return;
        }
        

        $default = App::getLocale();
        App::setLocale(getUserLanguage($user));

        $count = $notification->getCountHash();
        $body = $notification->getClassNotification()->getMessage($count);
        if (! $notification->is_system) {
            $body = $from->getName().' '.$body;
        }
        $payloadData = $notification->getClassNotification()->getPayloadData();
        $avatar = $from->getAvatar();
        $data = [
            'user_id' => (string)$user->id,
            'notification_id' => (string)$notification->id,
            'type' => $notification->getClassNotification()->getType(),
            'notify_data' => json_encode($payloadData),
            'url' => $notification->getHref()
        ];

        $this->pushNotify($user, $body, $avatar, $data, $notification->hash);

        App::setLocale($default);
    }

    public function uniqueId()
    {
        return $this->notification->hash;
    }
}
