<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Chatbot\Models\ChatbotHistory;
use Packages\ShaunSocial\Core\Models\Bookmark;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\HashtagFollow;
use Packages\ShaunSocial\Core\Models\Like;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\Report;
use Packages\ShaunSocial\Core\Models\SearchHistory;
use Packages\ShaunSocial\Core\Models\UserBlock;
use Packages\ShaunSocial\Core\Models\UserDelete;
use Packages\ShaunSocial\Core\Models\UserDownload;
use Packages\ShaunSocial\Core\Models\UserDownloadItem;
use Packages\ShaunSocial\Core\Models\UserFollow;
use Packages\ShaunSocial\Core\Models\UserHashtag;
use Packages\ShaunSocial\Core\Models\UserHashtagSuggest;
use Packages\ShaunSocial\Core\Models\UserList;
use Packages\ShaunSocial\Core\Models\UserListMember;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;
use Packages\ShaunSocial\Story\Models\Story;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Story\Models\StoryView;
use Packages\ShaunSocial\UserPage\Models\UserPageAdmin;
use Packages\ShaunSocial\UserPage\Models\UserPageFollowReport;
use Packages\ShaunSocial\UserPage\Models\UserPageInfo;
use Packages\ShaunSocial\UserVerify\Models\UserVerifyFile;

class UserDeleteRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:user_delete_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete user task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = UserDelete::orderBy('id')->limit(config('shaun_core.core.limit_delete'))->get();

        $users->each(function($user){
            $check = false;
            $commentCheck = false;
            // delete post
            $posts = Post::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($posts)) {
                $check = true;
                $commentCheck = true;

                $posts->each(function($post){
                    $post->delete();
                });
            }

            // delete comment
            if (! $commentCheck) {
                $comments = Comment::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
                if (count($comments)) {
                    $check = true;

                    $comments->each(function($comment){
                        $comment->delete();
                    });
                } else {
                    $replies = CommentReply::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
                
                    $replies->each(function($reply){
                        $reply->delete();
                    });
                }
            }

            //delete user follow and follower
            $userFollows = UserFollow::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($userFollows)) {
                $check = true;

                $userFollows->each(function($userFollow){
                    $userFollow->delete();
                });
            }

            $userFollowers = UserFollow::where('follower_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($userFollowers)) {
                $check = true;

                $userFollowers->each(function($userFollower){
                    $userFollower->delete();
                });
            }

            //delete notify user
            $notifications = UserNotification::where('from_id', $user->user_id)->select(DB::raw('min(id) as id'))->groupBy('user_id')->limit(setting('feature.item_per_page'))->get();
            if (count($notifications)) {
                $check = true;
                    
                $notifications->each(function($item) use ($user) {
                    $notification = UserNotification::findByField('id', $item->id);
                    UserNotification::where('from_id', $user->user_id)->where('user_id', $notification->user_id)->delete();
                });
            }

            //delete page admin
            if ($user->is_page) {
                $userPageAdmins = UserPageAdmin::where('user_page_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
                if (count($userPageAdmins)) {
                    $check = true;
                        
                    $userPageAdmins->each(function($item) {
                        $item->delete();
                    });
                }
            } else {
                $userPageAdmins = UserPageAdmin::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
                if (count($userPageAdmins)) {
                    $check = true;
                        
                    $userPageAdmins->each(function($item) {
                        if ($item->isPageOwner()) {
                            $this->userRepository->delete($item->getPage());
                        }
                        $item->delete();
                    });
                }
            }

            //delete group
            $members = GroupMember::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($members)) {
                $check = true;
                    
                $members->each(function($member) {
                    if ($member->isOwner()) {
                        $group = $member->getGroup();
                        if ($group) {
                            $group->delete();
                        }
                    } else {
                        $member->delete();
                    }
                });
            }

            //delete request group
            $groupRequests = GroupMemberRequest::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($groupRequests)) {
                $check = true;
                    
                $groupRequests->each(function($request) {
                    $request->delete();
                });
            }

            //delete user list
            $lists = UserList::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($lists)) {
                $check = true;
                    
                $lists->each(function($list) {
                    $list->delete();
                });
            }

            //delete user list member
            $listMembers = UserListMember::where('user_id', $user->user_id)->limit(setting('feature.item_per_page'))->get();
            if (count($listMembers)) {
                $check = true;
                    
                $listMembers->each(function($member) {
                    $member->delete();
                });
            }

            if (! $check) {
                //delete like
                Like::where('user_id', $user->user_id)->delete();

                //delete notify
                UserNotification::where('user_id', $user->user_id)->delete();

                //delete bookmark
                Bookmark::where('user_id', $user->user_id)->delete();

                //delete hashtag
                HashtagFollow::where('user_id', $user->user_id)->delete();
                UserHashtagSuggest::where('user_id', $user->user_id)->delete();
                UserHashtag::where('user_id', $user->user_id)->delete();

                //delete block
                UserBlock::where('user_id', $user->user_id)->delete();
                UserBlock::where('blocker_id', $user->user_id)->delete();

                //delete download data
                UserDownload::where('user_id', $user->user_id)->delete();
                UserDownloadItem::where('user_id', $user->user_id)->delete();

                //delete story
                Story::where('user_id', $user->user_id)->delete();
                StoryItem::where('user_id', $user->user_id)->delete();
                StoryView::where('user_id', $user->user_id)->delete();

                //delete report
                Report::where('user_id', $user->user_id)->delete();
                
                //delete verify
                $files = UserVerifyFile::getFilesByUserId($user->user_id);
                $files->each(function($file){
                    $file->delete();
                });
                
                if ($user->is_page) {
                    //delete page report
                    UserPageFollowReport::where('user_page_id', $user->user_id)->delete();
                    
                    //delete page info
                    UserPageInfo::where('user_page_id', $user->user_id)->delete();
                }

                //delete history chatbot
                ChatbotHistory::where('user_id', $user->user_id)->delete();

                //delete history search
                SearchHistory::where('user_id', $user->user_id)->delete();

                //delete two factor
                UserTwoFactor::where('user_id', $user->user_id)->delete();
                
                $user->delete();
            }
        });
    }
}
