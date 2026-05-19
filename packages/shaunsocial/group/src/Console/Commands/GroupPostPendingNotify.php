<?php


namespace Packages\ShaunSocial\Group\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupPostPending;
use Packages\ShaunSocial\Group\Notification\GroupPostPendingNotification;

class GroupPostPendingNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_group:group_post_pending_notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notify to admin of group when have a post pending.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $storyRepository = null;

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
        $postPendings = GroupPostPending::orderBy('id')->where('notify_sent', false)->limit(config('shaun_group.post_pending_limit_run'))->get();
        foreach ($postPendings as $postPending) {
            $members = GroupMember::getAdminList($postPending->group_id);
            foreach ($members as $member) {
                if ($member->checkNotifySetting('pending_post_notify')) {
                    $user = $member->getUser();
                    if ($user) {
                        Notification::send($user, $postPending->getUser(), GroupPostPendingNotification::class, $postPending, [], 'shaun_group', false, false);
                    }
                }
            }

            $postPending->update([
                'notify_sent' => true
            ]);
        }
    }
}
