<?php


namespace Packages\ShaunSocial\UserPage\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;
use Packages\ShaunSocial\UserPage\Models\UserPageNotificationCron;
use Packages\ShaunSocial\UserPage\Notification\UserPageMessageToAdminNotification;
use Packages\ShaunSocial\UserPage\Notification\UserPageNotifyToAdminNotification;

class UserPageNotificationCronRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_user_page:notification_cron_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Page run notification cron.';

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
        $crons = UserPageNotificationCron::orderBy('id')->limit(config('shaun_user_page.notification_limit_run'));
        $crons->each(function($cron){
            $checked = false;

            $page = $cron->getPage();
            if ($page) {
                $userAdmins = UserPageAdmin::where('user_page_id', $page->id)->where('id', '>', $cron->current)->orderBy('id')->limit(config('shaun_user_page.notification_limit_user_run'))->get();
                if (count($userAdmins)) {
                    $checked = true;
                    $current = 0;

                    foreach ($userAdmins as $userAdmin) {
                        $user = $userAdmin->getUser();
                        $current = $userAdmin->id;
                        if (! $user) {
                            $userAdmin->delete();
                            continue;
                        }

                        //send notify
                        switch ($cron->type) {
                            case 'page_notify':
                                Notification::send($user, $page, UserPageNotifyToAdminNotification::class, null, [], 'shaun_user_page');
                                break;
                            case 'message_notify':
                                Notification::send($user, $page, UserPageMessageToAdminNotification::class, null, [], 'shaun_user_page');
                                break;
                        }
                    }

                    $cron->update(['current' => $current]);
                }
            }

            if (! $checked) {
                $cron->delete();
            }
        });
    }
}
