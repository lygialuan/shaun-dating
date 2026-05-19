<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasCover;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Group\Enum\GroupType;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\HasShareEmail;
use Packages\ShaunSocial\Core\Traits\IsSource;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Group\Enum\GroupStatus;
use Packages\ShaunSocial\Group\Enum\GroupWhoCanPost;
use Packages\ShaunSocial\Group\Http\Resources\GroupResource;
use Packages\ShaunSocial\Group\Http\Resources\GroupSourceResource;

class Group extends Model
{
    use HasCacheQueryFields, HasStorageFiles, HasCover, IsSubject, IsSource, HasReport, HasShareEmail;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'name',
        'description',
        'categories',
        'hashtags',
        'type',
        'cover_file_id',
        'member_count',
        'post_approve_enable',
        'member_request_count',
        'post_pending_count',
        'cache_key',
        'block_count',
        'admin_count',
        'slug',
        'who_can_post',
        'is_popular',
        'status',
        'datetime_change_status'
    ];

    protected $storageFields = [
        'cover_file_id'
    ];

    protected $casts = [
        'type' => GroupType::class,
        'post_approve_enable' => 'boolean',
        'who_can_post' => GroupWhoCanPost::class,
        'is_popular' => 'boolean',
        'status' => GroupStatus::class
    ];

    protected $overviewMembers = null;

    public function getRules()
    {
        return GroupRule::findByField('group_id', $this->id, true);
    }

    public function getReportToUserId($userId = null)
    {
        return GroupMember::getOwner($this->id)->user_id;
    }

    public function getCoverDefault()
    {
        return setting('shaun_group.cover_default');
    }

    public function getHref()
    {
        if ($this->id) {
            return route('web.group.profile',[
                'id' => $this->id,
                'slug' => $this->slug
            ]);
        }
        
        return  '';
    }

    public function getMemberPendingHref()
    {
        if ($this->id) {
            return route('group.member_pending_post',[
                'id' => $this->id,
            ]);
        }
        
        return  '';
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function canView($viewerId)
    {
        if (! $this->checkStatus()) {
            return false;
        }
        
        if (GroupBlock::getBlock($viewerId, $this->id)) {
            return false;
        }

        switch ($this->type) {
            case GroupType::PRIVATE:
                $member = GroupMember::getMember($viewerId, $this->id);
                return $member ? true : false;
                break;
        }
        return true;
    }

    public function getCategories()
    {
        if ($this->categories) {
            $collection = Str::of($this->categories)->explode(' ');
            return $collection->map(function ($value, $key) {
                $category = GroupCategory::findByField('id', $value);
                if (! $category->canUse()) {
                    return null;
                } else {
                    return $category;
                }
            })->filter(function ($value, $key) {
                return $value;
            });
        }

        return [];
    }

    public function getTypeText()
    {
        switch ($this->type) {
            case GroupType::PUBLIC:
                return __('Public');
                break;
            case GroupType::PRIVATE:
                return __('Private');
                break;
        }
    }

    public function getHashtags()
    {
        $hashtags = collect();
        if ($this->hashtags) {
            $collection = Str::of($this->hashtags)->explode(' ');
            $hashtags = $collection->map(function ($value, $key) {
                return Hashtag::findByField('id', $value);
            });
        }

        return $hashtags;
    }

    public function getOverviewMembers()
    {
        if ($this->overviewMembers) {
            $members = $this->overviewMembers;
        } else {
            $members = GroupMember::getOverviewMembers($this->id);
        }
        
        return $members->filter(function ($member, $key) {
            return $member->getUser();
        });
    }

    public function setOverviewMembers($members)
    {
        $this->overviewMembers = $members;
    }

    public function getSourcePrivacy()
    {
        return $this->type->value;
    }

    public function checkPermissionPost($viewerId)
    {
        $member = GroupMember::getMember($viewerId, $this->id);
        return $member ? true : false;
    }

    public static function getResourceClass()
    {
       return GroupResource::class;
    }

    public function getSourceMemberLabel($userId)
    {
        $member = GroupMember::getMember($userId, $this->id);
        return $member && $member->isAdmin() ? __('Admin') : '';
    }

    public static function getSourceResourceClass()
    {
       return GroupSourceResource::class;
    }

    public function recentObjectForSource($subject) {
        switch ($subject->getSubjectType()) {
            case 'posts': 
                if ($this->post_approve_enable) {
                    if (! GroupMember::getAdmin($subject->user_id, $this->id)) {
                        $subject->update([
                            'show' => false
                        ]);

                        //Delete hashtag trending
                        GroupHashtagTrending::where('post_id',$subject->id)->delete();

                        GroupPostPending::create([
                            'user_id' => $subject->user_id,
                            'post_id' => $subject->id,
                            'post_type' => $subject->type,
                            'group_id' => $this->id,
                            'post_content' => $subject->content_search
                        ]);
                    }
                } else {
                    GroupStatistic::add($this->id, 'post',  $subject->getUser(), $subject, false);
                    GroupCron::create([
                        'user_id' => $subject->user_id,
                        'group_id' => $this->id,
                        'item_id' => $subject->id,
                        'type' => 'post_new'
                    ]);
                }
                break;
        }
    }

    public function deleteWithSource($subject)
    {
        switch ($subject->getSubjectType()) {
            case 'posts': 
                GroupStatistic::remove($this->id, 'post',  $subject->user_id, $subject);
                break;
        }
    }

    public function checkStatus()
    {
        return $this->status == GroupStatus::ACTIVE;
    }

    public function getStatisticOnDetail()
    {
        return Cache::remember('group_statistic_on_detail_'.$this->id, config('shaun_group.statistic_time'), function () {
            $previousWeek = strtotime("-1 week + 1 day");

            $startWeek = strtotime("last monday midnight",$previousWeek);
            $endWeek = strtotime("next sunday",$previousWeek);

            return [
                'post_recent_count' => GroupStatistic::where('group_id', $this->group_id)->where('type', 'post')->where('created_at', '>=', date('Y-m-d 00:00:01'))->count(),
                'post_this_month_count' => GroupStatistic::where('group_id', $this->group_id)->where('type', 'post')->where('created_at', '>=', date('Y-m-01 00:00:01'))->count(),
                'member_last_week_count' => GroupStatistic::where('group_id', $this->group_id)->where('type', 'member')->where('created_at', '>=', date('Y-m-d 00:00:01', $startWeek))->where('created_at', '<=', date('Y-m-d 23:59:59', $endWeek))->count(),
            ];
        });
    }

    public function canPost($viewerId)
    {
        if (! $viewerId) {
            return false;
        }

        if (! $this->canView($viewerId)) {
            return false;
        }

        if (GroupBlock::getBlock($viewerId, $this->id)) {
            return false;
        }

        switch ($this->who_can_post) {
            case GroupWhoCanPost::MEMBER:
                $member = GroupMember::getMember($viewerId, $this->id);
                return $member ? true : false;
                break;
            case GroupWhoCanPost::ADMIN:
                $admin = GroupMember::getAdmin($viewerId, $this->id);
                return $admin ? true : false;
                break;
        }

        return false;
    }

    public function getPostStatus($viewerId)
    {
        if (! $viewerId) {
            return 'login';
        }

        $member = GroupMember::getMember($viewerId, $this->id);
        if (! $member) {
            return 'member';
        }
        
        switch ($this->who_can_post) {
            case GroupWhoCanPost::ADMIN:
                $admin = GroupMember::getAdmin($viewerId, $this->id);
                if (!$admin) {
                    return 'admin';
                }
                
                break;
        }

        return 'ok';
    }

    public function canShareEmail($userId)
    {
        return $this->checkStatus();
    }

    public function canShareProfile($userId)
    {
        return $this->checkStatus();
    }

    public function canReport($userId)
    {
        return $this->checkStatus();
    }

    public static function getPostTypes()
    {
        $types = Post::getTypes();
        $result = [];
        foreach (['text', 'photo', 'link', 'video', 'file', 'poll'] as $key) {
            $result[$key] = $types[$key];
        }

        return $result;
    }

    public function canJoin($viewerId)
    {
        if (! $viewerId) {
            return false;
        }
        
        if (! $this->checkStatus()) {
            return false;
        }
        
        if (GroupBlock::getBlock($viewerId, $this->id)) {
            return false;
        }

        $member = GroupMember::getMember($viewerId, $this->id);
        if ($member) {
            return false;
        }

        if (GroupMemberRequest::getRequest($viewerId, $this->id)) {
            return false;
        }

        return true;
    }

    public function checkShowWithSource($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canEditWithSource($subject, $userId)
    {
        return $this->canDeleteWithSource($subject, $userId);
    }

    public function canDeleteWithSource($subject, $userId)
    {
        if (! $this->canViewWithSource($subject, $userId)) {
            return false;
        }

        if (! $this->post_approve_enable && $subject->user_id == $userId) {
            return true;
        }

        if (GroupMember::getAdmin($userId, $this->id)) {
            return true;
        }
        
        return false;
    }

    public function canCommentWithSource($subject, $userId)
    {
        if (! $this->canViewWithSource($subject, $userId)) {
            return false;
        }

        $member = GroupMember::getMember($userId, $this->id);
        
        return $member ? true : false;
    }

    public function canViewWithSource($subject, $userId)
    {
        if (! $subject->show) {
            return false;
        }
        return $this->canView($userId);
    }

    public function checkNotificationWithSource($userId)
    {
        return $this->canView($userId);
    }

    public function addStatisticWithSource($type, $viewer, $subject)
    {
        GroupStatistic::add($this->id, $type, $viewer, $subject);
    }

    public function doHashTagPostWithSource($type, $data, $post)
    {
        if (! $post->show) {
            return;
        }
        switch ($type) {
            case 'delete_all':
                GroupHashtagTrending::where('post_id',$post->id)->delete();
                break;
            case 'delete_for_update':
                GroupHashtagTrending::where('post_id', $post->id)->whereIn('hashtag_id', $data['hashtag_ids'])->delete();
                break;
            case 'add':
                $hashtag = $data['hastag'];
                GroupHashtagTrending::create([
                    'name' => $hashtag->name,
                    'hashtag_id' => $hashtag->id,
                    'is_active' => $hashtag->is_active,
                    'post_id' => $post->id,
                    'group_id' => $this->id
                ]);
                break;
        }

        GroupHashtagTrending::clearCache($this->id);
    }

    public static function getNew($limit)
    {
        return Cache::remember('group_get_new', config('shaun_group.wiget_time'), function () use ($limit) {
            return self::where('status', GroupStatus::ACTIVE)->limit($limit)->orderBy('id', 'DESC')->get();
        });
    }

    public static function getPopular()
    {
        return Cache::remember('group_get_popular', config('shaun_group.wiget_time'), function () {
            return self::where('status', GroupStatus::ACTIVE)->limit(config('shaun_core.core.number_item_random'))->orderBy('is_popular', 'DESC')->orderBy('member_count', 'DESC')->get();
        });
    }

    public static function clearCacheGroupAll()
    {
        self::clearCacheGroupNew();
        self::clearCacheGroupPopular();
    }

    public static function clearCacheGroupPopular()
    {
        Cache::forget('group_get_popular');
    }

    public static function clearCacheGroupNew()
    {
        Cache::forget('group_get_new');
    }

    public function getStatusText()
    {
        $status = GroupStatus::getALl();
        return $status[$this->status->value];
    }

    public function canHide()
    {
        return $this->status == GroupStatus::ACTIVE;
    }

    public function canOpen()
    {
        return $this->status == GroupStatus::HIDDEN;
    }

    public function canDisable()
    {
        return $this->status == GroupStatus::ACTIVE;
    }

    public function canActive()
    {
        return $this->status == GroupStatus::DISABLE;
    }

    public function canApprove()
    {
        return $this->status == GroupStatus::PENDING;
    }

    public function isAdminOfSource($userId)
    {
        if (! $this->canView($userId)) {
            return false;
        }

        if (GroupMember::getAdmin($userId, $this->id)) {
            return true;
        }
        
        return false;
    }

    public function canDeleteSubject()
    {
        return false;
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($group) {
            $group->cache_key = Str::random(8);
            $group->slug = Str::slug($group->name);
            $group->datetime_change_status = now();
            $group->status = setting('shaun_group.auto_approve') ? GroupStatus::ACTIVE : GroupStatus::PENDING;
        });

        static::created(function ($group) {
            $group->member_count = 1;
            self::clearCacheGroupAll();
        });

        static::saving(function($group) {
            if ($group->id) {
                foreach (['member_count', 'is_popular', 'name', 'cover_file_id'] as $value) {
                    if ($group->{$value} != $group->getOriginal($value)){
                        self::clearCacheGroupAll();
                    }
                }
            }
        });

        static::deleted(function ($group) {
            Post::where('source_id', $group->id)->where('source_type', 'groups')->delete();
            PostHome::where('source_id', $group->id)->where('source_type', 'groups')->delete();
            GroupMember::where('group_id' , $group->id)->delete();
            GroupBlock::where('group_id' , $group->id)->delete();
            GroupCron::where('group_id' , $group->id)->delete();
            GroupHashtagTrending::where('group_id' , $group->id)->delete();
            GroupMemberRequest::where('group_id' , $group->id)->delete();
            GroupRule::where('group_id' , $group->id)->delete();
            GroupCron::create([
                'group_id' => $group->id,
                'type' => 'delete'
            ]);

            self::clearCacheGroupAll();
        });
    }
}