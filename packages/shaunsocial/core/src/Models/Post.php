<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Advertising\Models\AdvertisingStatistic;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Notification\Post\PostCommentNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostCommentOfCommentNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostLikeNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostMentionCommentNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostMentionLikeNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostMentionNotification;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasComment;
use Packages\ShaunSocial\Core\Traits\HasLike;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\HasMention;
use Packages\ShaunSocial\Core\Traits\HasCacheShortUserHome;
use Packages\ShaunSocial\Core\Traits\HasBookmark;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasHistory;
use Packages\ShaunSocial\Core\Traits\HasNotificationStop;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\HasShareEmail;
use Packages\ShaunSocial\Core\Traits\HasUserHashtag;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Traits\IsSubjectNotification;
use Packages\ShaunSocial\UserPage\Models\UserPageStatistic;
use Packages\ShaunSocial\Core\Enum\CommentPrivacy;
use Packages\ShaunSocial\Core\Enum\PostPaidType;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;
use Packages\ShaunSocial\Core\Notification\Post\PostShareNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostUserFollowNotification;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Traits\HasContentTranslate;
use Packages\ShaunSocial\Core\Traits\HasSource;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Group\Models\GroupPostPending;
use Packages\ShaunSocial\PaidContent\Models\UserPostPaid;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;

class Post extends Model
{
    use HasLike, IsSubject, HasUser, HasComment, HasCachePagination, HasCacheQueryFields, HasMention, HasCacheShortUserHome, HasBookmark, 
        IsSubjectNotification, HasNotificationStop, HasUserList, HasReport, HasShareEmail, HasHistory, HasUserHashtag, HasSource, HasStorageFiles, HasContentTranslate;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'hashtags',
        'parent_id',
        'content_search',
        'user_privacy',
        'is_ads',
        'show',
        'poll_items',
        'comment_privacy',
        'content_warning_categories',
	    'view_count',
        'pin_date',
        'created_at',
        'stop_comment',
        'pin_profile_date',
		'thumb_file_id',
        'is_paid',
        'content_amount',
        'paid_type',
        'earn_amount'
    ];

    protected $isAds = false;

    protected $simpleResource = false;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $storageFields = [
        'thumb_file_id'
    ];

    protected $contentLanguageFields = [
        'content'
    ];

    protected $casts = [
        'is_ads' => 'boolean',
        'show' => 'boolean',
        'comment_privacy' => CommentPrivacy::class,
        'stop_comment' => 'boolean',
		'is_paid' => 'boolean',
        'paid_type' => PostPaidType::class
    ];

    protected $mentionField = 'content';

    protected $items = null;

    protected $hashtagList = null;

    protected $inType = '';

    protected $refCode = null;

    public function getListCachePagination()
    {
        $hashtags = $this->getHashtags();
        $result = [
            'user_profile_'.$this->user_id,
            'user_profile_media_'.$this->user_id,
            'user_profile_vibb_'.$this->user_id,
            'user_profile_paid_content_'.$this->user_id
        ];

        if ($hashtags) {
            foreach ($hashtags as $hashtag) {
                $result[] = 'hashtag_'.$hashtag->name;
            }
        }

        if ($this->has_source) {
            $result[] = $this->source_type.'_profile_'.$this->source_id;
            $result[] = $this->source_type.'_profile_media_'.$this->source_id;
        }

        return $result;
    }

    public function getListFieldPagination()
    {
        return [
            'like_count',
            'comment_count',
            'is_paid',
            'content',
            'is_ads',
            'show',
            'view_count',
            'pin_date',
            'pin_profile_date'
        ];
    }

    public function getItems()
    {
        if (in_array($this->type, ['text'])) {
            return [];
        }

        if (! $this->items) {
            $items = PostItem::findByField('post_id', $this->id, true);
            if ($items) {
                $items = $items->sortBy(function ($item) {
                    return $item->order;
                });
            }

            $this->items = $items;
        }

        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getHashtags()
    {
        if (! $this->hashtagList) {
            if ($this->hashtags) {
                $collection = Str::of($this->hashtags)->explode(' ');
                $this->hashtagList = $collection->map(function ($value, $key) {
                    return Hashtag::findByField('id', $value);
                });
            }
        }
        
        return $this->hashtagList;
    }

    public function setHashtags($hashtagList)
    {
        $this->hashtagList = $hashtagList;
    }

    public function canDelete($userId)
    {
        return ($this->isOwner($userId) && ! $this->is_ads) || $this->canEditWithSource($userId);
    }

    public function canStopComment($userId)
    {
        return $this->canEdit($userId);
    }

    public function canEdit($userId)
    {
        if ($this->has_source) {
            return $this->canEditWithSource($userId);
        }
        
        return $this->isOwner($userId);
    }

    public function canShare($userId)
    {
        return $userId && $this->canView($userId) && !$this->parent_id && !$this->has_source;
    }

    public function canChangeCommentPrivacy($userId)
    {
        return $this->isOwner($userId) && !$this->has_source;
    }
    
    public function canChangeContentWarning($userId)
    {
        return $this->isOwner($userId) && in_array($this->type, ['photo', 'video', 'vibb']);
    }

    public function canReport($userId)
    {
        return $this->canView($userId) && !$this->isOwner($userId);
    }

    public static function getTypes()
    {
        return [
            'text' => __('Text'),
            'photo' => __('Photo'),
            'link' => __('Link'),
            'share' => __('Share'),
            'video' => __('Video'),
            'file' => __('File'),
            'poll' => __('Poll'),
            'vibb' => __('Vibb'),
            'share_item' => __('Share item')
        ];
    }

    public function getParent()
    {
        if ($this->parent_id) {
            return self::findByField('id', $this->parent_id);
        }

        return null;
    }

    public function getDataForPostHome()
    {
        $data = $this->toArray();
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['comment_privacy']);
        unset($data['content_warning_categories']);
        $data['post_id'] = $this->id;

        return $data;
    }

    public function canSendMessage($viewerId) 
    {
        return $this->canView($viewerId);
    }

    public function canView($viewerId)
    {
        if ($this->has_source) {
            return $this->canViewWithSource($viewerId);
        } 

        return $this->getUser()->canView($viewerId);
    }

    public function canLike($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canBookmark($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canComment($viewerId)
    {
        if (! $viewerId) {
            return false;
        }

        if ($this->stop_comment) {
            return false;
        }
        
        if ($this->has_source) {
            return $this->canCommentWithSource($viewerId);
        }

        if ($this->isOwner($viewerId)){
            return true;
        }
        if (!$this->canView($viewerId)){
            return false;
        }

        $user = $this->getUser();
        $viewer = User::findByField('id', $viewerId);
        $mentionIds = $this->getMentionUserIds()->toArray();
        $mentioned = in_array($viewerId, $mentionIds);

        switch ($this->comment_privacy) {
            case CommentPrivacy::EVERYONE:
                return true;
            case CommentPrivacy::FOLLOWING:
                return $user->getFollow($viewerId) || $mentioned;
            case CommentPrivacy::VERIFIED:
                return (setting('user_verify.enable') ? $viewer->isVerify() : true) || $mentioned;
            case CommentPrivacy::MENTIONED:
                return $mentioned;
            default:
                return false;
        }
    }

    public function canViewComment($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function getHref()
    {
        return route('web.post.detail',[
            'id' => $this->id
        ]). ($this->refCode ? '?ref_code='.$this->refCode : '');
    }

    public function setRefCode($refCode)
    {
        if (! $this->refCode) {
            $this->refCode = $refCode;
        }
    }

    public function getRefCode()
    {
        return $this->refCode;
    }

    public function getTitle()
    {
        return __('Post');
    }

    public function sendCommentNotification($viewer, $comment)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('comment')) {
            Notification::send($this->getUser(), $viewer, PostCommentNotification::class, $comment);            
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('comment')) {
                Notification::send($user, $viewer, PostMentionCommentNotification::class, $comment);
            }
        }

        if ($viewer->id == $this->user_id) {
            $users = Distinct::where('user_hash', Distinct::getUserHash($comment->getDistinctValue(true)))->where('updated_at', '>', now()->subDays(config('shaun_core.notify.limit_day_owner_send')))->orderBy('updated_at', 'DESC')->limit(config('shaun_core.notify.limit_number_owner_send'))->get();
            if ($users) {
                $users = $this->filterUserList($users, $viewer);
            }

            if ($users) {
                $users->each(function($user) use ($viewer, $comment){
                    Notification::send($user->getUser(), $viewer, PostCommentOfCommentNotification::class, $comment);
                });              
            }
        }
    }

    public function sendLikeNotification($viewer)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('like')) {
            Notification::send($this->getUser(), $viewer, PostLikeNotification::class, $this);
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('like')) {
                Notification::send($user, $viewer, PostMentionLikeNotification::class, $this);
            }
        }
    }

    public function deleteLikeNotification($viewer)
    {
        UserNotification::deleteFromAndSubject($viewer,$this,PostLikeNotification::class);
        UserNotification::deleteFromAndSubject($viewer,$this,PostMentionLikeNotification::class);
    }
    
    static function getMentionNotificationClass()
    {
        return PostMentionNotification::class;
    }

    public static function getResourceClass()
    {
        return PostResource::class;
    }

    public function addHashtag($hashtags) 
    {
        $currentHashtags = $this->getHashtags();
        if (! $currentHashtags) {
            $currentHashtags = collect();
        }

        $arrayIds = $currentHashtags->pluck('id')->toArray();
        foreach ($hashtags as $hashtag) {
            $item = $currentHashtags->first(function ($value, $key) use ($hashtag) {
                return $value->name == $hashtag;
            });

            if (! $item) {
                
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);

                $item->increment('post_count');
            }

            $arrayIds[] = $item->id;
        }
        
        $this->update(['hashtags' => Arr::join(array_unique($arrayIds), ' ')]);
    }

    public static function getCountByUser($userId)
    {
        return self::where('user_id', $userId)->where('has_source', false)->count();
    }

    public static function getCountByUserAndPaid($userId)
    {
        return self::where('user_id', $userId)->where('is_paid', true)->count();
    }

    public function canShareEmail($userId)
    {
        return $this->canView($userId);
    }

    public static function getPostPaidFeatures($userId)
    {
        return Cache::remember(self::getKeyPostPaidFeatureCache($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            $posts = self::where('is_paid', true)->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit(config('shaun_paid_content.number_post_feature'))->get();

            return is_null($posts) ? false : $posts;
        });
    }

    public static function getKeyPostPaidFeatureCache($userId)
    {
        return 'post_paid_feature_'.$userId;
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($post) {
            if ($post->comment_privacy === null) {
                $post->comment_privacy = CommentPrivacy::EVERYONE;
            }

            if ($post->show === null) {
                $post->show = true;
            }

            if ($post->source_type) {
                $post->has_source = true;
                $source = $post->getSource();
                $post->source_privacy = $source->getSourcePrivacy();
            }

            $user = $post->getUser();
            if ($user && ! $post->has_source) {
                $count = Post::getCountByUser($post->user_id);
                $countPostPaid = $user->post_paid_count - 1;
                if ($post->is_paid) {
                    $countPostPaid = Post::getCountByUserAndPaid($post->user_id);
                }
                $user->update([
                    'post_count' => $count + 1,
                    'post_paid_count' => $countPostPaid + 1
                ]);
            }
        });

        static::deleted(function ($post) {
            $post->clearCache();
            $hashtags = $post->getHashtags();
            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    $hashtag->decrement('post_count');
                }
            }

            $items = $post->getItems();
            foreach ($items as $item) {
                $item->delete();
            }

            if ($post->has_source) {
                $post->doHashTagPostWithSource('delete_all', []);
            } else {
                HashtagTrending::where('post_id',$post->id)->delete();
            }
            

            if (!$post->parent_id) {
                self::where('parent_id', $post->id)->get()->each(function ($post) {
                    $post->delete();
                });
            }

            //delete post home
            $postHome = PostHome::where('post_id', $post->id)->first();
            if ($postHome) {
                $postHome->delete();
            }

            if ($post->is_ads) {
                $advertising = Advertising::findByField('post_id', $post->id);
                if ($advertising) {
                    $advertising->onDone(true);
                }
            }

            //delete group pending post
            $postPending = GroupPostPending::where('post_id', $post->id)->first();
            if ($postPending) {
                $postPending->delete();
            }

            //delete user paid post
            UserPostPaid::where('post_id', $post->id)->delete();
        });

        static::saving(function ($post) {
            if ($post->id) {
                if ($post->content != $post->getOriginal('content')) {
                    $content = $post->getMentionContent(null);
                    $mentions = getMentionsFromContent($content);
                    foreach ($mentions as $mention) {
                        $content = str_replace($mention, '', $content);
                    }
                    $post->content_search = $content;
                }
            } else {
                $post->content_search = $post->content;
            }

            if ($post->content) {
                if ($post->id && ($post->content == $post->getOriginal('content') || ! $post->getOriginal('content'))) {
                    return;
                }
                $hashtags = getHashtagsFromContent($post->content);
                $hashtagsKeep = [];
                $hashtagArray = [];
                $items = [];
                if ($post->id && $post->hashtags) {
                    $hashtagsCurrent = Str::of($post->hashtags)->explode(' ');
                    $hashtagsKeep = array_intersect($hashtags, $hashtagsCurrent->toArray());
                    $hashtagsDelete = array_diff($hashtagsCurrent->toArray(), $hashtags);
                    if (count($hashtagsDelete)){
                        $deleteIds = [];
                        foreach ($hashtagsDelete as $hashtagDelete) {
                            $item = Hashtag::findByField('id', $hashtagDelete);
                            if ($item) {
                                $item->decrement('post_count');
                                $deleteIds[] = $item->id;
                            }
                        }
                        if ($post->has_source) {
                            $post->doHashTagPostWithSource('delete_for_update', ['hashtag_ids' => $deleteIds]);
                        } else {
                            HashtagTrending::where('post_id', $post->id)->whereIn('hashtag_id', $deleteIds)->delete();
                        }
                        
                    }                    
                }
                foreach ($hashtags as $hashtag) {
                    $hashtag = str_replace('#', '', $hashtag);
                    
                    $item = Hashtag::firstOrCreate([
                        'name' => $hashtag,
                    ]);
                    if (! in_array($hashtag, $hashtagsKeep)) {
                        $item->increment('post_count');
                    }

                    $hashtagArray[] = $item->id;
                    $items[] = $item;
                }
                $post->hashtags = Arr::join(array_unique($hashtagArray), ' ');
                $post->setHashtags(collect($items));
                
                if ($post->id && count($items)) {
                    foreach ($items as $item) {
                        if ($post->has_source) {
                            $post->doHashTagPostWithSource('add', ['hastag' => $item]);
                        } else {
                            HashtagTrending::create([
                                'name' => $item->name,
                                'hashtag_id' => $item->id,
                                'is_active' => $item->is_active,
                                'post_id' => $post->id
                            ]);
                        }
                    }
                }
            }
        });

        static::updated(function ($post) {            
            //check update for post home
            $check = false;
            $fieldChange = ['content', 'hashtags', 'like_count', 'comment_count', 'privacy', 'show', 'is_paid'];
            foreach ($fieldChange as $field) {
                if ($post->{$field} != $post->getOriginal($field)) {
                    $check = true;
                    break;
                }
            }
            if ($check)
            {
                $data = $post->getDataForPostHome();
                $postHome = PostHome::where('post_id', $post->id)->first();
                if ($postHome) {
                    $postHome->update($data);
                }
            }
            
            $post->clearCache();
        });

        static::created(function ($post) {
            $post->view_count = 0;
            $hashtags = $post->getHashtags();

            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    if ($post->has_source) {
                        $post->doHashTagPostWithSource('add', ['hastag' => $hashtag]);
                    } else {
                        HashtagTrending::create([
                            'name' => $hashtag->name,
                            'hashtag_id' => $hashtag->id,
                            'is_active' => $hashtag->is_active,
                            'post_id' => $post->id
                        ]);
                    }
                }
            }

            //add to post home
            $data = $post->getDataForPostHome();
            PostHome::create($data);

            $post->clearCache();
        });

        static::deleting(function ($post) {
            $user = $post->getUser();
            if ($user && ! $post->has_source) {
                $count = Post::getCountByUser($post->user_id);
                $countPostPaid = $user->post_paid_count + 1;
                if ($post->is_paid) {
                    $countPostPaid = Post::getCountByUserAndPaid($post->user_id);
                }

                $user->update([
                    'post_count' => $count - 1,
                    'post_paid_count' => $countPostPaid - 1
                ]);
            }

            if ($post->has_source) {
                $post->deleteWithSource($post);
            }
        });
    }

    public static function updatePrivacy($userId, $privacy)
    {
        self::where('user_id', $userId)->update([
            'user_privacy' => $privacy
        ]);
    }

    public function getThumb()
    {
        if ($this->is_paid){
            if ($this->thumb_file_id) {
                return $this->getFile('thumb_file_id')->getUrl();
            } else {
                return setting('shaun_paid_content.thumb_default');
            }
        }

        return '';
    }

    public function getOgImage()
    {
        if ($this->is_paid){
            return $this->getThumb();
        }

        $items = $this->getItems();
        if ($items && count($items)) {
            $item = $items->first();
            return $item->getOgImage();
        }

        return '';
    }

    public function setIn($type, $value)
    {
        $this->inType = ''; 
        if ($value) {
            $this->inType = $type; 
        }
    }

    public function getIn($type)
    {
        return $this->inType == $type;
    }

    public function isPin()
    {
        if ($this->getIn('source') && $this->pin_date) {
            return true;
        }
        
        if ($this->getIn('home') && $this->pin_date) {
            return true;
        }

        if ($this->getIn('profile') && $this->pin_profile_date) {
            return true;
        }

        return false;
    }

	public function canShowContent($userId)
    {
        if ($this->isOwner($userId)) {
            return true;
        };

        if (in_array($this->type, ['photo', 'video']) && $this->is_paid) {
            if (! $userId) {
                return false;
            }
            $paid = UserPostPaid::getPaid($userId, $this->id);
            if ($paid) {
                return true;
            }
            switch ($this->paid_type) {
                case PostPaidType::PAYPERVIEW:
                    return false;
                    
                    break;
                case PostPaidType::SUBSCRIBER:
                    return UserSubscriber::getUserSubscriber($userId, $this->user_id);

                    break;
            }
        }

        return true;
    }

    public function getPaidItemContent()
    {
        if ($this->is_paid) {
            $items = $this->getItems();
            switch ($this->type) {
                case 'photo':
                    return count($items);
                    break;
                case 'video':
                    $item = $items->first();
                    return $item->getSubject()->duration;
                    break;
            }
        }

        return '';
    }

    public function addStatistic($type, $viewer)
    {
        $owner = $this->getUser();
        if ($owner) {
            if (! $viewer || $viewer->id != $owner->id) {
                $owner->addPageStatistic($type, $viewer, $this);
                PostStatistic::add($viewer, $type, $this->id);
            }
        }
    }

    public function getHistoryContentFirst()
    {
        switch ($this->type) {
            case 'photo':
                return __('Added photo(s) to this post.');
                break;
            case 'video':
                return __('Added a video to this post.');
                break;
            case 'link':
                return __('Added a link to this post.');
                break;
        }

        return '';
    }

    public function addAdvertisingStatistic($type, $viewerId, $ip = '')
    {
        if ($this->is_ads && $viewerId != $this->user_id) {
            $advertising = Advertising::findByField('post_id', $this->id);
            if ($advertising && $advertising->canAddStatistic()) {
                AdvertisingStatistic::add([
                    'type' => $type,
                    'user_id' => $viewerId,
                    'ip' => $ip,
                    'advertising_id' => $advertising->id
                ]);
                $advertising->addCheckDoneForReport();
            }
        }
    }

    public function canBoot($viewerId) {
        if ($this->is_ads) {
            return false;
        }

        if ($this->has_source) {
            return false;
        }
        
        if (! in_array($this->type, ['text', 'photo', 'link', 'video', 'file'])) {
            return false;
        }

        if ($this->isOwner($viewerId)) {
            return true;
        }

        $user = $this->getUser();
        if ($user->isPage()) {
            $admin = $user->getAdminPage($viewerId);
            if ($admin) {
                return true;
            }
        }
    
        return false;
    }

    public function setIsAdvertising($isAds)
    {
        $this->isAds = $isAds;
    }

    public function isAdvertising()
    {
        return $this->isAds;
    }

    public function getContentWarningCategories()
    {
        $content_warning = collect();
        if ($this->content_warning_categories) {
            $collection = Str::of($this->content_warning_categories)->explode(' ');
            $content_warning = $collection->map(function ($value, $key) {
                return ContentWarningCategory::findByField('id', $value);
            });
        }

        return $content_warning;
    }

    public function clearCache()
    {
        if ($this->has_source) {
            Cache::forget(self::getKeySourcePinCache($this->source_type, $this->source_id));
        } else {
            Cache::forget('home_pin');
            Cache::forget('profile_pin_'.$this->user_id);
        }
    }

    public static function getKeySourcePinCache($sourceType, $sourceId)
    {
        return $sourceType.'_pin_'.$sourceId;
    }

    public static function getPin($source)
    {
        return Cache::remember(self::getKeySourcePinCache($source->getSubjectType(), $source->id), config('shaun_core.cache.time.model_query'), function () use ($source) {
            $posts = self::where('source_type', $source->getSubjectType())->where('source_id', $source->id)->where('pin_date', '!=', 0)->orderBy('pin_date', 'DESC')->limit(config('shaun_core.core.number_item_pin'))->get();

            return is_null($posts) ? false : $posts;
        });
    }

    public function doAfterCreate()
    {
        $this->addToSource();
    }

    public function sendNotification()
    {
        // send notify
        $user = $this->getUser();
        $this->sendMentionNotification($user);
        
        //Send notify to follower
        if ($user->checkUserEnableFollowNotification() && ! $this->has_source) {
            UserFollowNotificationCron::create([
                'user_id' => $user->id,
                'subject_type' => $this->getSubjectType(),
                'subject_id' => $this->id,
                'class'=> PostUserFollowNotification::class
            ]);
        }

        //Send notify to parent post
        if ($this->type == 'share') {
            $parentPost = Post::findByField('id', $this->parent_id);
            if ($parentPost->user_id != $this->user_id) {
                if ($parentPost->getUser()->checkNotifySetting('share')) {
                    Notification::send($parentPost->getUser(), $user, PostShareNotification::class, $parentPost);
                }

                //add statistic for page
                $parentPost->getUser()->addPageStatistic('post_share', $user, $this, false);
            }
        }
    }

    public function checkNotification($userId)
    {
        if ($this->has_source) {
            if (! $this->checkNotificationWithSource($userId)) {
                return false;
            }
        }

        return ! $this->getNotificationStop($userId);
    }

    public function getTypeLabel()
    {
        switch ($this->type) {
            case 'vibb':
                return __('Vibb');
                break;
        }

        return '';
    }

    public function getTypeBoxLabel()
    {
        if ($this->is_paid) {
            switch ($this->paid_type) {
                case PostPaidType::SUBSCRIBER:
                    return __('For Subscribers');
                    break;
                case PostPaidType::PAYPERVIEW:
                    return __('Pay Per View').' ('.formatNumber($this->content_amount).' '.getWalletTokenName().')';
                    break;
            }
        }
        return $this->getSourceMemberLabel();
    }
    
    public function canPin($viewerId)
    {
        if (! $this->getIn('source')) {
            return false;
        }

        if ($this->pin_date) {
            return false;
        }

        if ($this->isAdminOfSource($viewerId)) {
            return true;
        }

        return false;
    }

    public function canUnPin($viewerId)
    {
        if (! $this->getIn('source')) {
            return false;
        }

        if (! $this->pin_date) {
            return false;
        }

        if ($this->isAdminOfSource($viewerId)) {
            return true;
        }

        return false;
    }

    public function canPinHome($viewer)
    {
        if (! $viewer || ! $viewer->isModerator()) {
            return false;
        }

        if ($this->pin_date) {
            return false;
        }

        if ($this->has_source) {
            return false;
        }

        return true;
    }

    public function canUnPinHome($viewer)
    {
        if (! $viewer || ! $viewer->isModerator()) {
            return false;
        }

        if (! $this->pin_date) {
            return false;
        }

        if ($this->has_source) {
            return false;
        }

        return true;
    }
    
    public function canPinProfile($viewer)
    {
        if (! $viewer || ! $this->isOwner($viewer->id)) {
            return false;
        }

        if ($this->pin_profile_date) {
            return false;
        }

        if ($this->has_source) {
            return false;
        }

        return true;
    }

    public function canUnPinProfile($viewer)
    {
        if (! $viewer || ! $this->isOwner($viewer->id)) {
            return false;
        }

        if (! $this->pin_profile_date) {
            return false;
        }

        if ($this->has_source) {
            return false;
        }

        return true;
    }

    public static function getPinHome()
    {
        return Cache::remember('home_pin', config('shaun_core.cache.time.model_query'), function () {
            $posts = self::where('pin_date', '!=', 0)->where('has_source', false)->orderBy('pin_date', 'DESC')->limit(config('shaun_core.core.number_item_pin'))->get();

            return is_null($posts) ? false : $posts;
        });
    }

    public static function getPinProfile($userId)
    {
        return Cache::remember('profile_pin_'.$userId, config('shaun_core.cache.time.model_query'), function () use ($userId) {
            $posts = self::where('pin_profile_date', '!=', 0)->where('user_id', $userId)->orderBy('pin_profile_date', 'DESC')->limit(config('shaun_core.core.number_item_pin'))->get();

            return is_null($posts) ? false : $posts;
        });
    }

	public function canPaid($userId)
    {
        if (! $userId) {
            return false;
        }

        if ($userId == $this->user_id) {
            return false;
        }

        if (! $this->is_paid) {
            return false; 
        }

        if ($this->paid_type != PostPaidType::PAYPERVIEW) {
            return false;
        }

        if (UserPostPaid::getPaid($userId, $this->post_id)) {
            return false;
        }

        return true;
    }

    public function setSimpleResuouce($isSimple)
    {
        $this->simpleResource = $isSimple;
    }

    public function isSimpleResource()
    {
        return $this->simpleResource;
    }

    public function canChangePaidContent($userId)
    {
        if (! $this->isOwner($userId)) {
            return false;
        }

        $user = $this->getUser();
        if (! $user->canCreatePostPaidContent()) {
            return false;
        }

        return (in_array($this->type, ['photo', 'video']));
    }

    public function makeThumbPurre()
    {
        if ($this->is_paid && ! $this->thumb_file_id) {
            $items = $this->getItems();
            $item = $items->first();
            switch ($this->type) {
                case 'photo':
                    $file = File::purrePhoto($item->getSubject(), [
                        'parent_type' => 'post_review',
                        'user_id' => $this->user_id,
                        'parent_id' => $this->id
                    ]);
                    if ($file) {
                        $this->update([
                            'thumb_file_id' => $file->id
                        ]);
                    }
                    break;
                case 'video':
                    $file = File::purrePhoto($item->getSubject()->getFile('thumb_file_id'), [
                        'parent_type' => 'post_review',
                        'user_id' => $this->user_id,
                        'parent_id' => $this->id
                    ]);
                    if ($file) {
                        $this->update([
                            'thumb_file_id' => $file->id
                        ]);
                    }
                    break;
            }
        }
    }
}
