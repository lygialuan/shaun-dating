<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Follow\UserFollowerResource;
use Packages\ShaunSocial\Core\Http\Resources\Follow\UserFollowingResource;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagFollowResource;
use Packages\ShaunSocial\Core\Models\HashtagFollow;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserFollow;
use Packages\ShaunSocial\Core\Models\UserNotification;
use Packages\ShaunSocial\Core\Notification\Follow\FollowNewNotification;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Support\Facades\Notification;

class FollowRepository
{
    use HasUserList;

    public function user_store($data, $viewer)
    {
        switch ($data['action']) {
            case 'follow':                
                $user = User::findByField('id', $data['id']);
                $viewer->addFollow($data['id'], $user->isPage());

                if (!setting('feature.max_follow_get_notification') || $user->follower_count <= setting('feature.max_follow_get_notification')) {                    
                    if ($user->checkNotifySetting('new_follow')) {
                        Notification::send($user, $viewer, FollowNewNotification::class, $viewer);
                    }
                }
                break;
            case 'unfollow':
                $follow = $viewer->getFollow($data['id']);
                $follow->delete();

                UserNotification::deleteFromAndSubject($viewer, $viewer,FollowNewNotification::class);
                break;
        }
    }

    public function user_get_follower($userId, $page, $type, $viewer)
    {
        $user = User::findByField('id', $userId);
        $condition = UserFollow::where('follower_id', $userId)->orderBy('id', 'DESC');
        $hasNextPage = false;
        $followers = [];
        switch ($type) {
            case 'all':
                $followers = UserFollow::getCachePagination('follower_'.$userId, $condition, $page);
                $hasNextPage = checkNextPage($user->follower_count, count($followers), $page);
                break;
            case 'user':
                $condition->where('user_is_page', false);
                $followers = UserFollow::getCachePagination('follower_user_'.$userId, $condition, $page);
                $followersNextPage = UserFollow::getCachePagination('follower_user_'.$userId, $condition, $page + 1);
                $hasNextPage = count($followersNextPage) ? true : false;
                break;
            case 'page':
                $condition->where('user_is_page', true);
                $followers = UserFollow::getCachePagination('follower_page_'.$userId, $condition, $page);
                $followersNextPage = UserFollow::getCachePagination('follower_page_'.$userId, $condition, $page + 1);
                $hasNextPage = count($followersNextPage) ? true : false;
                break;
        }
        

        return [
            'items' => UserFollowerResource::collection($this->filterUserList($followers, $viewer)),
            'has_next_page' => $hasNextPage
        ];
    }

    public function user_get_following($userId, $page, $type ,$viewer)
    {
        $user = User::findByField('id', $userId);
        $condition = UserFollow::where('user_id', $userId)->orderBy('id', 'DESC');
        $hasNextPage = false;
        $followings = [];
        switch ($type) {
            case 'all':
                $followings = UserFollow::getCachePagination('following_'.$userId, $condition, $page);
                $hasNextPage = checkNextPage($user->following_count, count($followings), $page);
                break;
            case 'user':
                $condition->where('follower_is_page', false);
                $followings = UserFollow::getCachePagination('following_user_'.$userId, $condition, $page);
                $followingsNextPage = UserFollow::getCachePagination('following_user_'.$userId, $condition, $page + 1);
                $hasNextPage = count($followingsNextPage) ? true : false;
                break;
            case 'page':
                $condition->where('follower_is_page', true);
                $followings = UserFollow::getCachePagination('following_page_'.$userId, $condition, $page);
                $followingsNextPage = UserFollow::getCachePagination('following_page_'.$userId, $condition, $page + 1);
                $hasNextPage = count($followingsNextPage) ? true : false;
                break;
        }

        return [
            'items' => UserFollowingResource::collection($this->filterUserList($followings, $viewer, 'follower_id')),
            'has_next_page' => $hasNextPage
        ];
    }

    public function hashtag_store($data, $viewer)
    {
        switch ($data['action']) {
            case 'follow':
                $viewer->addHashtagFollow($data['name']);
                break;
            case 'unfollow':
                $follow = $viewer->getHashtagFollow($data['name']);
                $follow->delete();
                break;
        }
    }

    public function hashtag($viewer, $page)
    {
        $hashtags = HashtagFollow::getCachePagination('hashtag_'.$viewer->id, HashtagFollow::where('user_id', $viewer->id)->orderBy('id', 'DESC'), $page);

        return [
            'items' => HashtagFollowResource::collection($hashtags),
            'has_next_page' => checkNextPage($viewer->hashtag_follow_count, count($hashtags), $page)
        ];
    }

    public function user_store_notification($data, $viewer)
    {
        $follow = $viewer->getFollow($data['id']);

        switch ($data['action']) {
            case 'add':
                $follow->update(['enable_notify' => 1]);
                break;
            case 'remove':
                $follow->update(['enable_notify' => 0]);
                break;
        }
    }
}
