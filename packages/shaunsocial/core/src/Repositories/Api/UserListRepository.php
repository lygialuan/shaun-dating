<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Enum\UserListMessageStatus;
use Packages\ShaunSocial\Core\Http\Resources\UserList\UserListMemberResource;
use Packages\ShaunSocial\Core\Http\Resources\UserList\UserListResource;
use Packages\ShaunSocial\Core\Models\UserList;
use Packages\ShaunSocial\Core\Models\UserListMember;
use Packages\ShaunSocial\Core\Models\UserListMessage;
use Packages\ShaunSocial\Core\Models\UserListMessageCron;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;

class UserListRepository
{
    use CacheSearchPagination;
    
    public function get_count($viewer)
    {
        return [
            'block_count' => $viewer->block_count,
            'follower_count' => $viewer->follower_count,
            'following_count' => $viewer->following_count,
            'subscriber_count' => $viewer->subscriber_count
        ];
    }

    public function get($page, $viewer)
    {
        $builder = UserList::where('user_id', $viewer->id)->orderBy('id', 'DESC');
        $lists = UserList::getCachePagination('user_list_'.$viewer->id, $builder, $page);
        $listsNextPage = UserList::getCachePagination('user_list_'.$viewer->id, $builder, $page + 1);

        return [
            'items' => UserListResource::collection($lists),
            'has_next_page' => count($listsNextPage) ? true : false
        ];
    }

    public function store($data, $viewer)
    {
        if (! empty($data['id'])) {
            $list = UserList::findByField('id', $data['id']);
            $list->update([
                'name' => $data['name']
            ]);
        } else {
            $list = UserList::create([
                'name' => $data['name'],
                'user_id' => $viewer->id,
            ]);
        }

        return new UserListResource($list);
    }

    public function delete($id)
    {
        $list = UserList::findByField('id', $id);
        $list->delete();
    }

    public function get_members($text, $page, $id)
    {
        $builder = UserListMember::where('user_list_id', $id)->select('user_list_members.*')->orderBy('user_list_members.id', 'DESC');
        if ($text) {
            $builder->join('users', function ($join) use ($text) {
                $join->on('users.id', '=', 'user_list_members.user_id')->where(function ($query) use ($text){
                    $query->where('users.name', 'LIKE', '%'.$text.'%')->orWhere('users.user_name', 'LIKE', '%'.$text.'%');
                });
            });
        }
        
        $key = 'user_list_get_members_'.$id.'_'.$text.'_'.$page;
        $members = $this->getCacheSearchShortPagination($key, $builder, $page);
        $membersNextPage = $this->getCacheSearchShortPagination($key, $builder, $page + 1);
        
        $users = $members->filter(function ($member, $key) {
            return $member->getUser();
        });

        return [
            'items' => UserListMemberResource::collection($users),
            'has_next_page' => count($membersNextPage) ? true : false
        ];
    }

    public function store_members($id, $userIds)
    {
        $list = UserList::findByField('id', $id);
        $memberCount = $list->getMemberCount();
        foreach ($userIds as $userId) {
            if (! UserListMember::checkExist($id, $userId)) {
                $memberCount ++;
                UserListMember::create([
                    'user_list_id' => $id,
                    'user_id' => $userId
                ]);
            }
        }

        $list->update([
            'member_count' => $memberCount
        ]);

        return new UserListResource($list); 
    }

    public function delete_member($id)
    {
        $member = UserListMember::findByField('id', $id);
        $member->delete();
    }

    public function send_message($data, $viewer)
    {
        if ($data['type'] == 'new_list') {
            $data['type'] = 'list';
            $list = UserList::create([
                'user_id' => $viewer->id,
                'name' => $data['name']
            ]);
            $data['list_id'] = $list->id;
            $this->store_members($list->id, $data['user_ids']);
        }

        if ($data['type'] == 'specific') {
            $data['status'] = UserListMessageStatus::DONE;  
        }

        $data['user_id'] = $viewer->id;

        $message = UserListMessage::create($data);

        if ($data['type'] == 'specific') {
            foreach ($data['user_ids'] as $userId) {
                UserListMessageCron::create([
                    'user_id' => $userId,
                    'message_id' => $message->id
                ]);
            }
        }
    }

    public function send_message_config($viewer)
    {
        $builder = UserList::where('user_id', $viewer->id)->where('member_count', '>', 0)->orderBy('id', 'DESC');
        $lists = UserList::getCachePagination('user_list_can_send_'.$viewer->id, $builder, 1);

        $result = collect();
        if ($viewer->follower_count > 0) {
            $result->add([
                'id' => 0,
                'type' => 'follower',
                'name' => __('Followers'),
                'member_count' => $viewer->follower_count
            ]);
        }

        if ($viewer->following_count > 0) {
            $result->add([
                'id' => 0,
                'type' => 'following',
                'name' => __('Followings'),
                'member_count' => $viewer->following_count
            ]);
        }

        if (setting('shaun_paid_content.enable') && $viewer->subscriber_count > 0) {
            $result->add([
                'id' => 0,
                'type' => 'subscriber',
                'name' => __('Subscribers'),
                'member_count' => $viewer->subscriber_count
            ]);
        }
        foreach ($lists as $list) {
            $result->add([
                'id' => $list->id,
                'type' => 'list',
                'name' => $list->name,
                'member_count' => $list->member_count
            ]);
        } 
        return $result;
    }

    public function search_for_send($query, $viewer)
    {
        $builder = UserList::where('user_id', $viewer->id)->where('member_count', '>', 0)->orderBy('id', 'DESC');
        if ($query) {
            $builder->where('name', 'LIKE', '%'.$query.'%');
        }

        $lists = $this->getCacheSearchShortPagination('user_list_can_send_search_'.$viewer->id.'_'.$query, $builder, 1);
        
        return UserListResource::collection($lists);
    }
}
