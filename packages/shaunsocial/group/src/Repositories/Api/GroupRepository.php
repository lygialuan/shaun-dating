<?php

namespace Packages\ShaunSocial\Group\Repositories\Api;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Group\Enum\GroupMemberRole;
use Packages\ShaunSocial\Group\Enum\GroupType;
use Packages\ShaunSocial\Group\Http\Resources\GroupCategoryResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupCoverResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupProfileResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupRuleResource;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupBlock;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupCategory;
use Packages\ShaunSocial\Group\Models\GroupCron;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;
use Packages\ShaunSocial\Group\Models\GroupRule;
use Packages\ShaunSocial\Group\Notification\GroupWelcomeNotification;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Group\Enum\GroupStatus;
use Packages\ShaunSocial\Group\Enum\GroupWhoCanPost;
use Packages\ShaunSocial\Group\Http\Resources\GroupBlockResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupMemberRequestResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupMemberResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupPostPendingResource;
use Packages\ShaunSocial\Group\Models\GroupPostPending;
use Packages\ShaunSocial\Group\Models\GroupStatistic;
use Packages\ShaunSocial\Group\Notification\GroupAddAdminNotification;
use Packages\ShaunSocial\Group\Notification\GroupPostPendingAcceptNotification;
use Packages\ShaunSocial\Group\Notification\GroupPostPendingDeleteNotification;
use Packages\ShaunSocial\Group\Notification\GroupRemoveAdminNotification;
use Packages\ShaunSocial\Group\Notification\GroupTransferOwnerNotification;
use Packages\ShaunSocial\Group\Traits\Utility as GroupUtility;

class GroupRepository
{
    use Utility, GroupUtility;

    public function get_categories()
    {
        $categoryAll = GroupCategory::getAll();
        $categories = $categoryAll->map(function ($category, $key) {
            if (! $category->canUse()) {
                return null;
            } else {
                if ($category->childs) {
                    $category->childs = $category->childs->map(function($category, $key) {
                        if (! $category->canUse()) {
                            return null;
                        } else {
                            return $category;
                        }
                    })->filter(function ($value, $key) {
                        return $value;
                    });
                }
                return $category;
            }
        })->filter(function ($value, $key) {
            return $value;
        });
        
        return GroupCategoryResource::collection($categories);
    }

    public function delete($groupId)
    {
        $group = Group::findByField('id', $groupId);
        $group->delete();
    }

    public function store($data, $viewer)
    {
        $hashtagArray = [];
        if ($data['hashtags']) {
            foreach ($data['hashtags'] as $hashtag) {
                if ($hashtag) {
                    $item = Hashtag::firstOrCreate([
                        'name' => $hashtag,
                    ]);
                    $hashtagArray[] = $item->id;
                }
            }
        }
    
        $group = Group::create([
            'name' => $data['name'],
            'hashtags' =>  Arr::join(array_unique($hashtagArray), ' '),
            'categories' => Arr::join(array_unique($data['categories']), ' '),
            'description' => $data['description'],
            'type' => $data['type']
        ]);

        $member = GroupMember::create([
            'user_id' => $viewer->id,
            'group_id' => $group->id,
            'role' => GroupMemberRole::OWNER
        ]);

        $group->setOverviewMembers(collect([$member]));
        
        return new GroupResource($group);
    }

    public function store_rule($data)
    {
        if ($data['id']) {
            $rule = GroupRule::findByField('id', $data['id']);
            unset($data['id']);
            $rule->update($data);
        } else {
            unset($data['id']);
            $last = GroupRule::latest()->first();
            if ($last) {
                $data['order'] = $last->id + 1;
            }
            GroupRule::create($data);
        }
    }

    public function upload_cover($file, $id, $viewer)
    {
        $group = Group::findByField('id', $id);
        
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'group_cover',
            'user_id' => $viewer->id,
            'parent_id' => $id
        ]);

        $group->update([
            'cover_file_id' => $storageFile->id
        ]);

        return new GroupCoverResource($group);
    }

    public function store_name($name, $id)
    {
        $group = Group::findByField('id', $id);
        $group->update([
            'name' => $name
        ]);
    }

    public function store_description($description, $id)
    {
        $group = Group::findByField('id', $id);
        $group->update([
            'description' => $description
        ]);
    }

    public function store_category($categories, $id)
    {
        $group = Group::findByField('id', $id);
        $group->update([
            'categories' => Arr::join(array_unique($categories), ' '),
        ]);
    }

    public function store_hashtag($hashtags, $id)
    {
        $group = Group::findByField('id', $id);
        $hashtagArray = [];
        if ($hashtags) {
            foreach ($hashtags as $hashtag) {
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);
                $hashtagArray[] = $item->id;
            }
        }

        $group->update([
            'hashtags' =>  Arr::join(array_unique($hashtagArray), ' '),
        ]);
    }

    public function store_type_private($id)
    {
        $group = Group::findByField('id', $id);

        $group->update([
            'type' =>  GroupType::PRIVATE
        ]);

        PostHome::where('source_id', $id)->where('source_type', 'groups')->update([
            'source_privacy' => GroupType::PRIVATE
        ]);
    }

    public function store_setting($data, $id)
    {
        $group = Group::findByField('id', $id);

        $group->update([
            'post_approve_enable' => $data['post_approve_enable'],
            'who_can_post' => $data['who_can_post'],
            'slug' => $data['slug']
        ]);
    }

    public function store_join($id, $viewer)
    {
        $group = Group::findByField('id', $id);
        $result = ['request_sent' => false, 'request_id' => 0]; 
        if ($group->type == GroupType::PRIVATE) {
            $request = GroupMemberRequest::create([
                'group_id' => $id,
                'user_id' => $viewer->id
            ]);
            
            GroupCron::create([
                'type' => 'join_request',
                'group_id' => $id,
                'user_id' => $viewer->id,
                'item_id' => $request->id
            ]);

            $result['request_sent'] = true;
            $result['request_id'] = $request->id;
        } else {
            GroupMember::create([
                'user_id' => $viewer->id,
                'group_id' => $id
            ]);
        }

        return $result;
    }

    public function get_join_request($text, $page, $id)
    {
        $builder = GroupMemberRequest::where('group_id', $id)->select('group_member_requests.*')->orderBy('group_member_requests.id', 'DESC');
        if ($text) {
            $builder->join('users', function ($join) use ($text) {
                $join->on('users.id', '=', 'group_member_requests.user_id')->where(function ($query) use ($text){
                    $query->where('users.name', 'LIKE', '%'.$text.'%')->orWhere('users.user_name', 'LIKE', '%'.$text.'%');
                });
            });
        }
        
        $key = 'group_get_join_requests_'.$id.'_'.$text.'_'.$page;
        $members = $this->getCacheSearchGroupPagination($key, $builder, $page);
        $membersNextPage = $this->getCacheSearchGroupPagination($key, $builder, $page + 1);
        
        $users = $members->filter(function ($member, $key) {
            return $member->getUser();
        });

        return [
            'items' => GroupMemberRequestResource::collection($users),
            'has_next_page' => count($membersNextPage) ? true : false
        ];
    }

    public function remove_join_request($id)
    {
        $request = GroupMemberRequest::findByField('id', $id);
        $request->delete();
    }

    public function accept_join_request($id)
    {
        $request = GroupMemberRequest::findByField('id', $id);
        $group = $request->getGroup();
        $user = $request->getUser();
        GroupMember::create([
            'user_id' => $request->user_id,
            'group_id' => $group->id
        ]);
        
        Notification::send($user, $user, GroupWelcomeNotification::class, $group, ['is_system' => true], 'shaun_group', false);

        $request->delete();
    }

    public function accept_multi_join_request($requestIds, $id)
    {
        $group = Group::findByField('id', $id);
        $memberCount = $group->member_count;
        $users = [];
        foreach ($requestIds as $id) {
            $request = GroupMemberRequest::findByField('id', $id);
            $users[] = $request->getUser();
            GroupMember::create([
                'user_id' => $request->user_id,
                'group_id' => $group->id
            ]);
        }
        GroupMemberRequest::whereIn('id', $requestIds)->delete();

        $group->update([
            'member_request_count' => $group->member_request_count - count($requestIds),
            'member_count' => $memberCount + count($requestIds),
            'cache_key' => Str::random(8)
        ]);

        foreach ($users as $user) {
            Notification::send($user, $user, GroupWelcomeNotification::class, $group, ['is_system' => true], 'shaun_group', false);
        }
    }

    public function delete_multi_join_request($requestIds, $id)
    {
        $group = Group::findByField('id', $id);
        GroupMemberRequest::whereIn('id', $requestIds)->delete();

        $group->update([
            'member_request_count' => $group->member_request_count - count($requestIds),
            'cache_key' => Str::random(8)
        ]);
    }

    public function delete_join_request($id)
    {
        $request = GroupMemberRequest::findByField('id', $id);
        $request->delete();
    }

    public function delete_all_join_request($id)
    {
        $group = Group::findByField('id', $id);
        GroupMemberRequest::where('group_id', $id)->delete();
        $group->update([
            'member_request_count' => 0,
            'cache_key' => Str::random(8)
        ]);
    }

    public function get_my_post_pending($id, $page, $viewer)
    {
        $builder = GroupPostPending::where('user_id', $viewer->id)->where('group_id', $id)->orderBy('created_at', 'DESC');
        $postPendings = GroupPostPending::getCachePagination('group_post_pending_user_'.$id.'_'.$viewer->id, $builder, $page);

        $postPendings = $postPendings->filter(function ($item, $key) {
            return $item->getPost();
        });
        
        return [
            'items' => GroupPostPendingResource::collection($postPendings),
            'has_next_page' => checkNextPage(GroupPostPending::getCountByUser($viewer->id, $id), count($postPendings), $page)
        ];
    }

    public function delete_my_post_pending($id)
    {
        $postPending = GroupPostPending::findByField('id', $id);
        $post = $postPending->getPost();
        if ($post) {
            $post->delete();
        } else {
            $postPending->delete();
        }
    }

    public function get_post_pending($data)
    {
        $builder = GroupPostPending::where('group_id', $data['id']);
        if (! empty($data['user_id'])) {
            $builder->where('user_id', $data['user_id']);
        }

        if (! empty($data['type'])) {
            $builder->where('post_type', $data['type']);
        }

        if (! empty($data['query'])) {
            $builder->whereFullText('post_content', $data['query']);
        }

        if ($data['from_date']) {
            $builder->where('created_at', '>=', $data['from_date']. ' 00:00:00');
        }
        if ($data['to_date']) {
            $builder->where('created_at', '<=', $data['to_date']. ' 23:59:59');
        }

        if ($data['sort'] == 'last') {
            $builder->orderBy('id', 'DESC');
        } else {
            $builder->orderBy('id', 'ASC');
        }

        $page = 1;
        if (! empty($data['page'])) {
            $page = $data['page'];
        }
        $key = md5(json_encode($data));
        $postPendings = $this->getCacheSearchGroupPagination('group_get_post_pending_'.$key, $builder, $page);
        $postPendingsNextPage = $this->getCacheSearchGroupPagination('group_get_post_pending_'.$key, $builder, $page + 1);
        $postPendings = $postPendings->filter(function ($item, $key) {
            return $item->getPost();
        });
        
        return [
            'items' => GroupPostPendingResource::collection($postPendings),
            'has_next_page' => count($postPendingsNextPage) ? true : false
        ];
    }

    public function delete_post_pending($id, $viewer)
    {
        $postPending = GroupPostPending::findByField('id', $id);
        $post = $postPending->getPost();
        $user = $postPending->getUser();
        if ($post) {
            $post->delete();
        } else {
            $postPending->delete();
        }
        if ($user) {
            Notification::send($user, $viewer, GroupPostPendingDeleteNotification::class, $postPending->getGroup(), [], 'shaun_group', false);
        }
    }

    public function accept_post_pending($id, $viewer)
    {
        $postPending = GroupPostPending::findByField('id', $id);
        $post = $postPending->getPost();
        $user = $postPending->getUser();
        if ($post) {
            $post->update([
                'created_at' => now(),
                'show' => true
            ]);

            $hashtags = $post->getHashtags();
            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    $post->doHashTagPostWithSource('add', ['hastag' => $hashtag]);
                }
            }
        }
        if ($user) {
            Notification::send($user, $viewer, GroupPostPendingAcceptNotification::class, $postPending->getGroup(), [], 'shaun_group', false);
        }
        $postPending->delete();

        GroupStatistic::add($post->id, 'post',  $post->getUser(), $post, false);
        GroupCron::create([
            'user_id' => $post->user_id,
            'group_id' => $postPending->group_id,
            'item_id' => $post->id,
            'type' => 'post_new'
        ]);
    }

    public function store_leave($id, $viewer)
    {
        $member = GroupMember::getMember($viewer->id, $id);
        $member->delete();
    }

    public function get_rule($id)
    {
        $rules = GroupRule::getByGroup($id);
        $rules->each(function ($item, int $key) {
            $item->setNumber($key + 1);
        });

        return GroupRuleResource::collection($rules);
    }

    public function get_profile($id)
    {
        $group = Group::findByField('id', $id);
        
        return new GroupProfileResource($group);
    }

    public function get_post($id, $page, $viewer)
    {
        $group = Group::findByField('id', $id);
        
        $posts = collect();
        if ($page == 1) {
            $pinPosts = Post::getPin($group);
            $pinPosts->each(function($post) use ($posts){
                $postNew = Post::findByField('id', $post->id);
                if ($postNew) {
                    $posts->add($postNew);
                }
            });
        }

        $profilePosts = Post::getCachePagination('groups_profile_'.$id, Post::where('source_id', $id)->where('source_type', 'groups')->where('show', true)->where('pin_date',0)->orderBy('created_at', 'DESC'), $page);
        $profilePosts->each(function($post) use ($posts){
            $posts->add($post);
        });
        $posts = $this->filterPostListForSource($posts, $viewer, true);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }

    public function get_post_with_hashtag($id, $name, $page, $viewer)
    {
        $hashtag = Hashtag::findByField('name', $name);
        $builder = Post::where('source_id', $id)->where('source_type', 'groups')->where('show', true)->whereFullText('hashtags', $hashtag->id)->orderBy('created_at', 'DESC');
        $posts = $this->getCacheSearchGroupPagination('group_get_post_hashtag_'.$id.'_'.$name, $builder, $page);
        
        $posts = $this->filterPostListForSource($posts, $viewer, true);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }

    public function get_media($id, $page, $viewer)
    {
        $posts = Post::getCachePagination('groups_profile_media_'.$id, Post::where('source_id', $id)->where('source_type', 'groups')->whereIn('type', array('photo', 'video'))->where('show', true)->orderBy('created_at', 'DESC'), $page);
        $posts = $this->filterPostListForSource($posts, $viewer, true);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }

    public function get_explore($page, $viewer)
    {
        $builder = PostHome::where('source_type','groups')->where('source_privacy', GroupType::PUBLIC)->where('show', true)->orderBy('created_at', 'DESC');

        $results = Cache::remember('group_explore_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push($post);
            }            
        }
        $posts = $this->filterPostListForSource($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function store_transfer_owner($id, $userId, $viewer)
    {
        $group = Group::findByField('id', $id);
        $user = User::findByField('id', $userId);
        $member = GroupMember::getMember($userId, $id);
        $memberOwner = GroupMember::getMember($viewer->id, $id);
        $request = GroupMemberRequest::getRequest($userId, $id);
        if ($request) {
            $request->delete();
        }
        if ($member) {
            if ($member->isAdmin()) {
                $group->update([
                    'admin_count' => $group->admin_count - 1
                ]);
            }

            $member->update([
                'role' => GroupMemberRole::OWNER
            ]);

            $memberOwner->update([
                'role' => GroupMemberRole::MEMBER
            ]);
        } else {
            $memberOwner->update([
                'role' => GroupMemberRole::MEMBER,
                'notify_setting' => ''
            ]);

            GroupMember::create([
                'group_id' => $id,
                'user_id' => $userId,
                'role' => GroupMemberRole::OWNER
            ]);
        }

        Notification::send($user, $viewer, GroupTransferOwnerNotification::class, $group, [], 'shaun_group');
    }

    public function search_user_for_admin($query, $id, $viewer)
    {
        $key = 'group_search_user_for_admin_'.$query.'_'.$id;
        $users = Cache::remember($key, config('shaun_core.cache.time.short'), function () use ($query, $id, $viewer) {
            $builder = User::where(function($select) use ($query) {
                $select->orWhere('user_name', 'LIKE', '%'.$query.'%');
                $select->orWhere('name', 'LIKE', '%'.$query.'%');
            })
            ->where('id', '!=', $viewer->id)->where('is_active', true)
            ->whereNotIn('id', function($select) use ($id) {
                $select->from('group_blocks')
                    ->select('user_id')
                    ->where('group_id', $id);
            })->limit(setting('feature.item_per_page'))->orderBy('name');
            
            return $builder->get();
        });

        return UserResource::collection($users);
    }

    public function add_admin($id, $userId, $viewer)
    {
        $group = Group::findByField('id', $id);
        $user = User::findByField('id', $userId);
        $request = GroupMemberRequest::getRequest($userId, $id);
        if ($request) {
            $request->delete();
        }

        $member = GroupMember::getMember($userId, $id);
        if ($member) {
            $group->update([
                'admin_count' => $group->admin_count + 1
            ]);

            $member->update([
                'role' => GroupMemberRole::ADMIN
            ]);
        } else {
            $member = GroupMember::create([
                'user_id' => $userId,
                'group_id' => $id,
                'role' => GroupMemberRole::ADMIN
            ]);
        }

        Notification::send($user, $viewer, GroupAddAdminNotification::class, $group, [], 'shaun_group');

        return new GroupMemberResource($member);
    }

    public function remove_admin($id, $viewer)
    {
        $member = GroupMember::findByField('id', $id);
        $group = $member->getGroup();

        $group->update([
            'admin_count' => $group->admin_count - 1
        ]);
        $member->update([
            'role' => GroupMemberRole::MEMBER
        ]);

        if ($viewer->id != $member->user_id) {
            Notification::send($member->getUser(), $viewer, GroupRemoveAdminNotification::class, $group, [], 'shaun_group');
        }
    }

    public function get_your_feed($page, $viewer)
    {
        if ($viewer->group_count > 0) {
            $builder = PostHome::orderBy('created_at', 'DESC')->where('show', true);
            if ($viewer->group_count > config('shaun_core.source.max_query_join')) {
                $builder->where(function($query) use ($viewer) {
                    $query->where('source_type','groups');
                    $query->whereIn('source_id', function($select) use ($viewer) {
                        $select->from('group_members')
                            ->select('group_id')
                            ->where('user_id', $viewer->id);
                    });
                    $query->where('has_source', true);
                });
            } else {
                $builder->where(function($query) use ($viewer) {
                    $query->where('source_type','groups');
                    $query->whereIn('source_id', GroupMember::getGroupIdsByUser($viewer->id));
                    $query->where('has_source', true);
                });
            }
            
            $results = Cache::remember('group_explore_'.$viewer->id.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
                return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
            });
    
            $posts = collect();
            foreach ($results as $result) {
                $post = Post::findByField('id', $result->post_id);
                if ($post) {
                    $posts->push($post);
                }            
            }
            $posts = $this->filterPostListForSource($posts, $viewer);
            $posts->each(function($post) use ($viewer){
                $post->addStatistic('post_reach', $viewer);
            });

            return PostResource::collection($posts);
        }

        return [];
    }

    public function get_for_you($page, $viewer)
    {
        $groups = collect();
        $groupsNextPage = [];

        $builder = Group::where('status', GroupStatus::ACTIVE)->orderBy('id', 'DESC');
        $hashtagRelative = $viewer->getHastagRelative();
        if ($hashtagRelative) {
            $builder->whereFullText('hashtags',implode(' ',$hashtagRelative));

            $groups = $this->getCacheSearchGroupPagination('groups_for_you_'.$viewer->id, $builder, $page);
            $groupsNextPage = $this->getCacheSearchGroupPagination('groups_for_you_'.$viewer->id, $builder, $page + 1);
        }

        return [
            'items' => GroupResource::collection($this->filterGroupList($groups, $viewer)),
            'has_next_page' => count($groupsNextPage) ? true : false
        ];
    }

    public function get_all($data, $viewer)
    {
        $page = $data['page'];
        $builder = Group::where('status', GroupStatus::ACTIVE)->orderBy('id', 'DESC');
        if ($data['keyword']) {
            $builder->where('name', 'LIKE', '%'.$data['keyword'].'%');
        }
        if ($data['category']) {
            $builder->whereFullText('categories', $data['category']);
        }

        $groups = $this->getCacheSearchGroupPagination('group_all_'.$data['keyword'].'_'.$data['category'], $builder, $page);
        $groupsNextPage = $this->getCacheSearchGroupPagination('group_all_'.$data['keyword'].'_'.$data['category'], $builder, $page + 1);

        return [
            'items' => GroupResource::collection($this->filterGroupList($groups, $viewer)),
            'has_next_page' => count($groupsNextPage) ? true : false
        ];
    }

    public function store_rule_order($orders)
    {
        foreach ($orders as $order => $id) {
            $rule = GroupRule::findByField('id', $id);
            $rule->update([
                'order' => $order + 1,
            ]);
        }
    }

    public function delete_rule($id)
    {
        $rule = GroupRule::findByField('id', $id);
        $rule->delete();
    }

    public function get_admin($page, $id)
    {
        $builder = GroupMember::where('group_id', $id)->where('role', [GroupMemberRole::ADMIN])->orderBy('id', 'ASC');
        $admins = GroupMember::getCachePagination('group_admin_'.$id, $builder, $page);
        $adminsNextPage = GroupMember::getCachePagination('group_admin_'.$id, $builder, $page + 1);

        $users = $admins->filter(function ($admin, $key) {
            return ! $admin->isOwner();
        });

        $results = [
            'items' => GroupMemberResource::collection($users),
            'has_next_page' => count($adminsNextPage) ? true : false
        ];

        return $results;
    }

    public function remove_member($id)
    {
        $member = GroupMember::findByField('id', $id);
        $member->delete();
    }

    public function get_members($text, $page, $id)
    {
        $builder = GroupMember::where('group_id', $id)->select('group_members.*')->where('role', GroupMemberRole::MEMBER)->orderBy('group_members.id', 'DESC');
        if ($text) {
            $builder->join('users', function ($join) use ($text) {
                $join->on('users.id', '=', 'group_members.user_id')->where(function ($query) use ($text){
                    $query->where('users.name', 'LIKE', '%'.$text.'%')->orWhere('users.user_name', 'LIKE', '%'.$text.'%');
                });
            });
        }
        
        $key = 'group_get_members_'.$id.'_'.$text.'_'.$page;
        $members = $this->getCacheSearchGroupPagination($key, $builder, $page);
        $membersNextPage = $this->getCacheSearchGroupPagination($key, $builder, $page + 1);
        
        $users = $members->filter(function ($member, $key) {
            return $member->getUser();
        });

        return [
            'items' => GroupMemberResource::collection($users),
            'has_next_page' => count($membersNextPage) ? true : false
        ];
    }

    public function get_blocks($text, $page, $id)
    {
        $builder = GroupBlock::where('group_id', $id)->select('group_blocks.*')->orderBy('group_blocks.id', 'DESC');
        if ($text) {
            $builder->join('users', function ($join) use ($text) {
                $join->on('users.id', '=', 'group_blocks.user_id')->where(function ($query) use ($text){
                    $query->where('users.name', 'LIKE', '%'.$text.'%')->orWhere('users.user_name', 'LIKE', '%'.$text.'%');
                });
            });
        }
        
        $key = 'group_get_blocks_'.$id.'_'.$text.'_'.$page;
        $members = $this->getCacheSearchGroupPagination($key, $builder, $page);
        $membersNextPage = $this->getCacheSearchGroupPagination($key, $builder, $page + 1);
        
        $users = $members->filter(function ($member, $key) {
            return $member->getUser();
        });

        return [
            'items' => GroupBlockResource::collection($users),
            'has_next_page' => count($membersNextPage) ? true : false
        ];
    }

    public function store_block($id, $userId)
    {
        $member = GroupMember::getMember($userId, $id);
        if ($member) {
            $member->delete();
        }

        GroupBlock::create([
            'group_id' => $id,
            'user_id' => $userId
        ]);
    }

    public function remove_block($id)
    {
        $block = GroupBlock::findByField('id', $id);
        $block->delete();
    }

    public function store_notify_setting($data, $id, $viewer)
    {
        $group = Group::findByField('id', $id);
        $member = GroupMember::getMember($viewer->id, $id);
        $notifySettingKeys = array_keys($member->getNotifySetting());
        $notifySettings = [];
        foreach ($notifySettingKeys as $key) {
            $notifySettings[$key] = $data[$key];
        }
        $member->update(['notify_setting' => json_encode($notifySettings)]);
    }

    public function store_pin($id, $postId, $action, $viewer)
    {
        $post = Post::findByField('id', $postId);
        if ($action == 'pin') {
            $post->update(['pin_date' => now()->timestamp]);
    
            GroupCron::create([
                'user_id' => $viewer->id,
                'group_id' => $id,
                'item_id' => $postId,
                'type' => 'pin_post'
            ]);
        } else {
            $post->update(['pin_date' => 0]);

            $cron = GroupCron::where('item_id', $postId)->where('type', 'pin_post')->first();
            if ($cron) {
                $cron->delete();
            }
        }

        $post->clearCache();
    }

    public function get_report_overview($id)
    {
        $data = Cache::remember('group_report_overview_'.$id, config('shaun_group.statistic_time'), function () use ($id){
            $date = now()->subDays(config('shaun_group.report_sub_day'));
        
            return [
               'engagement' => GroupStatistic::whereIn('type', ['comment', 'like'])->where('group_id', $id)->where('created_at', '>=', $date)->count(),
               'member_new' => GroupStatistic::where('type', 'member')->where('group_id', $id)->where('created_at', '>=', $date)->count(),
               'member_active' => GroupMember::where('group_id', $id)->where('last_active', '>=', $date)->count(),
            ];
        });

        return $data;
    }

    public function get_report_chart($id)
    {
        return Cache::remember('group_report_chart_'.$id, config('shaun_group.statistic_time'), function () use ($id){
            $start = now()->subDays(config('shaun_group.chart_day') + 1);
            $members = [];
            $posts = [];
            for ($i = 0; $i <= config('shaun_group.chart_day'); $i++) {
                $date = $start->copy()->addDays($i);
                $key = $date->toFormattedDateString();
                $members[$key] = GroupStatistic::where('group_id', $id)->where('type', 'member')->whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->count();
                $posts[$key] = GroupStatistic::where('group_id', $id)->where('type', 'post')->whereBetween('created_at', [$date->startOfDay(), $date->copy()->endOfDay()])->count();
            }

            return [
                'member' => ['label' => array_keys($members), 'data' => array_values($members)],
                'post' => ['label' => array_keys($posts), 'data' => array_values($posts)],
            ];
        });
    }

    public function get_admin_manage_config($id, $viewer)
    {
        $member = GroupMember::getMember($viewer->id, $id);
        $group = Group::findByField('id', $id);
        $owner = GroupMember::getOwner($id);

        return [
            'is_owner' => $member->isOwner() ? true : false,
            'group' => new GroupResource($group),
            'who_can_post' => $group->who_can_post,
            'post_approve_enable' => $group->post_approve_enable,
            'member_request_count' => $group->member_request_count,
            'post_pending_count' => $group->post_pending_count,
            'block_count' => $group->block_count,
            'member_count' => $group->member_count,
            'admin_count' => $group->admin_count,
            'member_without_admin' => $group->member_count - 1 - $group->admin_count,
            'owner' => new GroupMemberResource($owner),
            'whoCanPostList' => GroupWhoCanPost::getAll(),
            'postTypes' =>['' => __('All content type')] + Group::getPostTypes()
        ];
    }

    public function get_user_manage_config($id, $viewer)
    {
        $group = Group::findByField('id', $id);
        return [
            'group' => new GroupResource($group),
            'user_post_pending_count' => GroupPostPending::getCountByUser($viewer->id, $id),
        ];
    }

    public function get_new($viewer, $limit)
    {
        $groups = Group::getNew($limit);

        $groups = $this->filterGroupList($groups, $viewer);
        
        return GroupResource::collection($groups);
    }

    public function get_popular($viewer, $limit)
    {
        $groups = Group::getPopular();

        $groups = $this->filterGroupList($groups, $viewer);

        if (count($groups) > $limit) {
            $groups = $groups->random($limit);
        }

        return GroupResource::collection($groups);
    }

    public function store_hide($id)
    {
        $group = Group::findByField('id', $id);
        $group->update([
            'status' => GroupStatus::HIDDEN
        ]);

        PostHome::where('source_id', $id)->where('source_type', 'groups')->update([
            'source_privacy' => GroupType::HIDDEN
        ]);
    }

    public function store_open($id)
    {
        $group = Group::findByField('id', $id);
        $group->update([
            'status' => GroupStatus::ACTIVE
        ]);

        PostHome::where('source_id', $id)->where('source_type', 'groups')->update([
            'source_privacy' => $group->type->value
        ]);
    }

    public function get_manage_group($status, $page, $viewer)
    {
        $builder = GroupMember::where('user_id', $viewer->id)->select('group_members.*')->whereIn('role', [GroupMemberRole::ADMIN, GroupMemberRole::OWNER]);
        $builder->join('groups', function ($join) use ($status) {
            $join->on('groups.id', '=', 'group_members.group_id')->orderBy('datetime_change_status', 'DESC');
            if ($status != 'all') {
                $join->where('status', $status);
            }
        });;

        $groups = $this->getCacheSearchGroupPagination('group_manage_'.$status. '_'. $viewer->id, $builder, $page);
        $groupsNextPage = $this->getCacheSearchGroupPagination('group_manage_'.$status. '_'. $viewer->id, $builder, $page + 1);

        $groups = $groups->map(function ($member, $key) {
            return Group::findByField('id', $member->group_id);
        });

        return [
            'items' => GroupResource::collection($groups),
            'has_next_page' => count($groupsNextPage) ? true : false
        ];
    }

    public function get_joined($page, $viewer)
    {
        $builder = GroupMember::where('user_id', $viewer->id)->select('group_members.*')->orderBy('group_members.id', 'DESC');
        $builder->join('groups', function ($join) {
            $join->on('groups.id', '=', 'group_members.group_id')->where('status', GroupStatus::ACTIVE);
        });
        
        $groups = $this->getCacheSearchGroupPagination('group_joined_'. $viewer->id, $builder, $page);
        $groupsNextPage = $this->getCacheSearchGroupPagination('group_joined_'. $viewer->id, $builder, $page + 1);

        $groups = $groups->map(function ($member, $key) {
            return Group::findByField('id', $member->group_id);
        });

        return [
            'items' => GroupResource::collection($this->filterGroupList($groups, $viewer)),
            'has_next_page' => count($groupsNextPage) ? true : false
        ];
    }

    public function search_post($id, $query, $page ,$viewer)
    {
        $builder = Post::where('source_id', $id)->where('source_type', 'groups')->where('show', true)->whereFullText('content_search', $query)->orderBy('created_at', 'DESC');
        $posts = $this->getCacheSearchGroupPagination('group_get_post_search_'.$id.'_'.$query, $builder, $page);
        
        $posts = $this->filterPostListForSource($posts, $viewer, true);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        
        return PostResource::collection($posts);
    }
}
