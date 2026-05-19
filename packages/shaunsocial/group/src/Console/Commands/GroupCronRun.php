<?php


namespace Packages\ShaunSocial\Group\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupCron;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;
use Packages\ShaunSocial\Group\Models\GroupPostPending;
use Packages\ShaunSocial\Group\Notification\GroupMemberRequestJoinNotification;
use Packages\ShaunSocial\Group\Notification\GroupPostNewNotification;
use Packages\ShaunSocial\Group\Notification\GroupPostPinNotification;

class GroupCronRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_group:group_cron_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notify to member of group when have admin have a post.';

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
        $crons = GroupCron::orderBy('id')->limit(config('shaun_group.post_notification_limit_run'));
        $crons->each(function($cron) {
            $cronUser = $cron->getUser();
            if (! $cronUser) {
                $cron->delete();
                return true;
            }
            switch ($cron->type) {
                case 'delete':
                    $postPendings = GroupPostPending::where('group_id' , $cron->group_id)->get();
                    if (count($postPendings)){
                        $postPendings->each(function($postPending) {
                            $post = $postPending->getPost();
                            if ($post) {
                                $post->delete();
                            }
                            $postPending->delete();
                        });
                    } else {
                        $cron->delete();
                        return true;
                    }
                    break;
                case 'join_request':
                    $request = GroupMemberRequest::findByField('id', $cron->item_id);
                    if ($request) {
                        $group = $request->getGroup();
                        if ($group) {
                            $members = GroupMember::getAdminList($request->group_id);
                            foreach ($members as $member) {
                                if ($member->checkNotifySetting('request_join_notify')) {
                                    $user = $member->getUser();
                                    if ($user) {
                                        Notification::send($user, $cronUser, GroupMemberRequestJoinNotification::class, $request, [], 'shaun_group', false, false);
                                    }
                                }
                            }
                        }
                    }
                    $cron->delete();
                    break;
                case 'pin_post':
                    $post = Post::findByField('id', $cron->item_id);
                    if (! $post) {
                        $cron->delete();
                        return true;
                    }

                    if (! $post->pin_date) {
                        $cron->delete();
                        return true;
                    }

                    $groupMembers = GroupMember::where('group_id', $cron->group_id)->where('user_id', '!=', $cron->user_id)->where('id', '>', $cron->current)->orderBy('id')->limit(config('shaun_group.post_notification_limit_run'))->get();

                    if ($groupMembers) {
                        $current = 0;
                        foreach ($groupMembers as $groupMember) {
                            $user = $groupMember->getUser();
                            $current = $groupMember->id;
                            if ($user) {
                                if ($groupMember->checkNotifySetting('pin_post')) {
                                   Notification::send($user, $cronUser, GroupPostPinNotification::class, $post, [], 'shaun_group', false, false);
                                }
                            } else {
                                $groupMember->delete();
                            }                  
                        }
                        $cron->update(['current' => $current]);
                    }

                    if (!$groupMembers || $groupMembers->count() < config('shaun_group.post_notification_limit_run')) {
                        $cron->delete();
                    }
    
                    break;
                case 'post_new':
                    $post = Post::findByField('id', $cron->item_id);
                    if (! $post) {
                        $cron->delete();
                        return true;
                    }

                    $groupMembers = GroupMember::where('group_id', $cron->group_id)->where('user_id', '!=', $cron->user_id)->where('id', '>', $cron->current)->orderBy('id')->limit(config('shaun_group.post_notification_limit_run'))->get();

                    if ($groupMembers) {
                        $current = 0;
                        foreach ($groupMembers as $groupMember) {
                            $user = $groupMember->getUser();
                            $current = $groupMember->id;
                            if ($user) {
                                if ($groupMember->checkNotifySetting('post_new')) {
                                    Notification::send($user, $cronUser, GroupPostNewNotification::class, $post, [], 'shaun_group', false, false);
                                }
                            } else {
                                $groupMember->delete();
                            }                  
                        }
                        $cron->update(['current' => $current]);
                    }

                    if (!$groupMembers || $groupMembers->count() < config('shaun_group.post_notification_limit_run')) {
                        $cron->delete();
                    }
    
                    break;

            }
            
        });
    }
}
