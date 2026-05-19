<?php


namespace Packages\ShaunSocial\UserPage\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Models\UserFollow;
use Packages\ShaunSocial\UserPage\Models\UserPageFollowReport;
use Packages\ShaunSocial\UserPage\Models\UserPageFollowReportUpdate;

class UserPageReportUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_user_page:report_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Page report update task.';

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
        $updates = UserPageFollowReportUpdate::orderBy('id')->limit(config('shaun_user_page.report_update_limit_run'));
        $updates->each(function($update){
            $user = $update->getUser();
            $checked = false;
            if ($user) {
                $users = UserFollow::where('user_id', $update->user_id)->where('id', '>', $update->current)->where('follower_is_page', true)->orderBy('id')->limit(config('shaun_user_page.report_update_limit_page_run'))->get();
                if (count($users)) {
                    $checked = true;
                    $current = 0;
                    $ids = [];
                    foreach ($users as $userFollow) {
                        $current = $userFollow->id;
                        $ids[] = $userFollow->follower_id;
                    }
                    $data = [
                        'birthday' => $user->getBirthdayYear(),
                        'gender_id' => $user->gender_id
                    ];
                    UserPageFollowReport::where('user_id', $user->id)->whereIn('user_page_id', $ids)->update($data);
                    $update->update(['current' => $current]);
                }
            }

            if (! $checked) {
                $update->delete();
            }
        });
    }
}
