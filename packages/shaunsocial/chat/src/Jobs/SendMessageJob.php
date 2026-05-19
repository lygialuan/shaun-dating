<?php


namespace Packages\ShaunSocial\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Packages\ShaunSocial\Core\Traits\Utility;

class SendMessageJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Utility;

    protected $member;
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member, $message)
    {
        $this->member = $member;
        $this->message = $message;        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $member = $this->member;
        $message = $this->message;

        $user = $member->getUser();
        $from = $message->getUser();
        if ($member->isActive() || ! $member->canPushNotify() || ! $user || ! $from) {
            return;
        }

        $default = App::getLocale();
        App::setLocale(getUserLanguage($user));
        $body = '['.__('Message').'] '. $message->getShortContent();
        $avatar = $from->getAvatar();
        $data = [
            'user_id' => $user->id,
            'room_id' => $member->room_id,
            'type' => 'send_message',
            'notify_data' => json_encode([]),
            'url' => $member->getRoom()->getHref()
        ];

        if ($member->isWebActive() && $member->isAppActive()) {
            $this->pushNotify($user, $body, $avatar, $data, 'room_'.$member->room_id);
        } else {
            if ($member->isWebActive()) {
                $this->pushNotify($user, $body, $avatar, $data, 'room_'.$member->room_id, ['android', 'ios']);
            }
            if ($member->isAppActive()) {
                $this->pushNotify($user, $body, $avatar, $data, 'room_'.$member->room_id, ['web']);
            }
        }
        
        
        App::setLocale($default);
    }

    public function uniqueId()
    {
        return $this->member->id;
    }
}
