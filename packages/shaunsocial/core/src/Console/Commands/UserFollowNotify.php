<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Models\UserFollow;
use Packages\ShaunSocial\Core\Models\UserFollowNotificationCron;

class UserFollowNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:user_follow_notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notify to user follow task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $crons = UserFollowNotificationCron::orderBy('id')->limit(config('shaun_core.follow.notify.limit_run'));
        $crons->each(function($cron){
            $subject = $cron->getSubject();
            $user = $cron->getUser();
            if (!$subject || !$user) {
                $cron->delete();
                return true;
            }
            $users = UserFollow::where('follower_id', $cron->user_id)->where('enable_notify', true)->where('id', '>', $cron->current)->orderBy('id')->limit(config('shaun_core.follow.notify.limit_send'))->get();
            if ($users) {
                $current = 0;
                foreach ($users as $userFollow) {
                    $current = $userFollow->id;
                    $user = User::findByField('id', $userFollow->user_id);
                    if ($user) {
                        if ($user->has_active) {
                            Notification::send($user, $cron->getUser(), $cron->class, $cron->getSubject(), [], $cron->package, false, false);
                        }
                    } else {
                        $userFollow->delete();
                    }                    
                }
                $cron->update(['current' => $current]);
            }

            if (!$users || $users->count() < config('shaun_core.follow.notify.limit_send')) {
                $cron->delete();
            }            
        });
    }
}
