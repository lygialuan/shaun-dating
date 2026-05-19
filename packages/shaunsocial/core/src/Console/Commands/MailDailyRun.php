<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Notification\NotificationResource;
use Packages\ShaunSocial\Core\Models\UserDailyEmail;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Traits\Utility;

class MailDailyRun extends Command
{
    use Utility;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:mail_daily_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mail daily run task.';

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
        $dailyEmails = UserDailyEmail::where('created_at', '<', now()->subDays(config('shaun_core.mail_daily.day')))->limit(config('shaun_core.mail_daily.limit_run'));
        $dailyEmails->each(function($dailyEmail){
            $user = $dailyEmail->getUser();

            if ($user && ! $user->getMailUnsubscribe()) {
                $default = App::getLocale();
                App::setLocale(getUserLanguage($user));

                $builder = UserNotification::where('user_id', $user->id)->select(DB::raw('max(id) as notify_id'))->where('is_viewed', false)->groupBy('hash')->where('created_at', '>', $dailyEmail->created_at)->orderBy('notify_id', 'DESC')->limit(config('shaun_core.mail_daily.max_notify_count'));
                $results = $builder->get();
                if (count($results)) {
                    $notifications = $this->filterNotification($results, $user->id);
    
                    $data = NotificationResource::collection($notifications);
    
                    Mail::send('daily_email', $user, [
                        'blade' => 'shaun_core::mail.type.daily_email',
                        'notifications' => json_decode($data->toJson(),true)
                    ]);
                }

                App::setLocale($default);
            }

            $dailyEmail->delete();
        });
    }
}
