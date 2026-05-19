<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentResource;
use Packages\ShaunSocial\Core\Notification\Comment\CommentLikeNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentMentionLikeNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentMentionNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentMentionReplyNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentReplyNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentReplyOfReplyNotification;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasLike;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\HasMention;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasContentTranslate;
use Packages\ShaunSocial\Core\Traits\HasDistinct;
use Packages\ShaunSocial\Core\Traits\HasHistory;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Traits\IsSubjectNotification;

class Comment extends Model
{
    use HasUser, HasLike, IsSubject, HasCachePagination, HasCacheQueryFields, HasSubject, HasMention, IsSubjectNotification, HasUserList, HasReport, HasHistory, HasDistinct, HasContentTranslate;

    protected $fillable = [
        'user_id',
        'content',
        'reply_count',
        'type'
    ];

    protected $cacheQueryFields = [
        'id',
    ];

    protected $contentLanguageFields = [
        'content'
    ];

    protected $mentionField = 'content';

    public function getDistinctValue($search = false)
    {
        if (! $this->getSubject()) {
            return null;
        }

        if (! $search && $this->getSubject()->user_id == $this->user_id) {
            return null;            
        }

        return [
            'type' => $this->getSubjectType(),
            'user_id' => $this->user_id,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
        ];
    }

    public function getLastDistinct()
    {
        return self::where('user_id', $this->user_id)->where('subject_type', $this->subject_type)->where('subject_id', $this->subject_id)->where('id', '!=', $this->id)->orderBy('id', 'DESC')->first();
    }

    public function getListCachePagination()
    {
        return [
            'comment_'.$this->subject_type.'_'.$this->subject_id,
        ];
    }

    public function getListFieldPagination()
    {
        return [
            'like_count',
            'reply_count',
            'content'
        ];
    }

    public function canDelete($userId)
    {
        return $this->isOwner($userId);
    }

    public function canEdit($userId)
    {
        return $this->isOwner($userId);
    }

    public function canLike($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canView($viewerId) 
    {
        return $this->getSubject()->canView($viewerId);
    }

    public function canReport($userId)
    {
        return $this->canView($userId) && !$this->isOwner($userId);
    }

    public function sendLikeNotification($viewer)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('like')) {
            Notification::send($this->getUser(), $viewer, CommentLikeNotification::class, $this);
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('like')) {
                Notification::send($user, $viewer, CommentMentionLikeNotification::class, $this);
            }
        }
    }

    public function deleteLikeNotification($viewer)
    {
        UserNotification::deleteFromAndSubject($viewer,$this,CommentLikeNotification::class);
        UserNotification::deleteFromAndSubject($viewer,$this,CommentMentionLikeNotification::class);
    }

    public function sendReplyNotification($viewer, $reply)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('reply')) {
            Notification::send($this->getUser(), $viewer, CommentReplyNotification::class, $reply);
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('reply')) {
                Notification::send($user, $viewer, CommentMentionReplyNotification::class, $reply);
            }
        }

        if ($viewer->id == $this->user_id) {
            $users = Distinct::where('user_hash', Distinct::getUserHash($reply->getDistinctValue(true)))->where('updated_at', '>', now()->subDays(config('shaun_core.notify.limit_day_owner_send')))->orderBy('updated_at', 'DESC')->limit(config('shaun_core.notify.limit_number_owner_send'))->get();
            if ($users) {
                $users = $this->filterUserList($users, $viewer);
            }

            if ($users) {
                $users->each(function($user) use ($viewer, $reply){
                    Notification::send($user->getUser(), $viewer, CommentReplyOfReplyNotification::class, $reply);
                }); 
            }
        }
    }
    
    static function getMentionNotificationClass()
    {
        return CommentMentionNotification::class;
    }

    public function checkNotification($userId)
    {
        return $this->getSubject()->checkNotification($userId);
    }

    public static function getResourceClass()
    {
        return CommentResource::class;
    }

    public function getReplyCount()
    {
        return CommentReply::where('comment_id', $this->id)->count();
    }

    public function getHref()
    {
        return $this->getSubject()->getHref().'/'. $this->id;
    }

    public function getTitle()
    {
        return __('Comment');
    }

    protected static function booted()
    {
        parent::booted();
        
        static::creating(function ($comment) {
            //increase
            $subject = $comment->getSubject();
            $count = $subject->getCommentCount();
            $subject->update([
                'comment_count' => $count + 1
            ]);
            $comment->reply_count = 0;
        });

        static::created(function ($comment) {
            $subject = $comment->getSubject();
            //add statistic for page
            if (method_exists($subject, 'getUser') && in_array($comment->subject_type, ['posts'])) {
                $user = $comment->getUser();
                $owner = $subject->getUser();
                if ($user && $owner && $user->id != $owner->id) {
                    $owner->addPageStatistic('post_comment', $user, $comment, false);
                }
            }

            //add statistic for source
            if (method_exists($subject, 'supportSource') && $subject->supportSource()){
                $user = $comment->getUser();
                $subject->addStatisticWithSource('comment', $user, $comment);
            }
        });

        static::deleting(function ($comment) {
            //decrease
            $subject = $comment->getSubject();
            if ($subject) {
                $count = $subject->getCommentCount();
                $subject->update([
                    'comment_count' => $count - 1,
                    'reply_count' => $subject->reply_count - $comment->reply_count
                ]);
            }

            CommentReply::where('comment_id', $comment->id)->delete();
        });
    }

    public function getItems()
    {
        if (in_array($this->type, ['text'])) {
            return [];
        }
        
        if (! $this->items) {
            $items = CommentItem::findByField('comment_id', $this->id, true);
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

    public static function getTypes()
    {
        return [
            'text',
            'photo',
            'link'
        ];
    }
}
