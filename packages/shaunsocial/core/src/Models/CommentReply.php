<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentReplyResource;
use Packages\ShaunSocial\Core\Notification\Comment\CommentReplyLikeNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentReplyMentionLikeNotification;
use Packages\ShaunSocial\Core\Notification\Comment\CommentReplyMentionNotification;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasLike;
use Packages\ShaunSocial\Core\Traits\HasMention;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasContentTranslate;
use Packages\ShaunSocial\Core\Traits\HasDistinct;
use Packages\ShaunSocial\Core\Traits\HasHistory;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\IsSubjectNotification;

class CommentReply extends Model
{
    use HasUser, HasLike, IsSubject, HasCachePagination, HasCacheQueryFields, HasMention, IsSubjectNotification, HasReport, HasHistory, HasDistinct, HasContentTranslate;

    protected $fillable = [
        'user_id',
        'comment_id',
        'content',
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
        if (! $this->getComment()) {
            return null;
        }
        
        if (! $search && $this->getComment()->user_id == $this->user_id) {
            return null;            
        }

        return [
            'type' => $this->getSubjectType(),
            'user_id' => $this->user_id,
            'subject_type' => 'comments',
            'subject_id' => $this->comment_id,
        ];
    }

    public function getLastDistinct()
    {
        return self::where('user_id', $this->user_id)->where('comment_id', $this->comment_id)->where('id', '!=', $this->id)->orderBy('id', 'DESC')->first();
    }

    public function getListCachePagination()
    {
        return [
            'reply_'.$this->comment_id,
        ];
    }

    public function getListFieldPagination()
    {
        return [
            'like_count',
            'content'
        ];
    }

    public function getComment()
    {
        return Comment::findByField('id', $this->comment_id);
    }

    public function canDelete($userId)
    {
        return $this->isOwner($userId);
    }

    public function canEdit($userId)
    {
        return $this->isOwner($userId);
    }

    public function canReport($userId)
    {
        return !$this->isOwner($userId);
    }

    public function canView($userId)
    {
        return $this->getComment()->canView($userId);
    }

    public function sendLikeNotification($viewer)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('like')) {
            Notification::send($this->getUser(), $viewer, CommentReplyLikeNotification::class, $this);
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('like')) {
                Notification::send($user, $viewer, CommentReplyMentionLikeNotification::class, $this);
            }
        }
    }

    public function deleteLikeNotification($viewer)
    {
        UserNotification::deleteFromAndSubject($viewer,$this,CommentReplyLikeNotification::class);
        UserNotification::deleteFromAndSubject($viewer,$this,CommentReplyMentionLikeNotification::class);
    }

    static function getMentionNotificationClass()
    {
        return CommentReplyMentionNotification::class;
    }

    public function checkNotification($userId)
    {
        return $this->getComment()->checkNotification($userId);
    }

    public static function getResourceClass()
    {
        return CommentReplyResource::class;
    }

    public function getHref()
    {
        return $this->getComment()->getHref().'/'. $this->id;
    }

    public function getTitle()
    {
        return __('Reply');
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($reply) {
            //increase
            $comment = $reply->getComment();
            $subject = $comment->getSubject();
            if ($subject) {
                $count = $subject->getReplyCount();
                $subject->update([
                    'reply_count' => $count + 1
                ]);
            }
            $count = $comment->getReplyCount();
            $comment->update([
                'reply_count' => $count + 1
            ]);            
        });

        static::deleting(function ($reply) {
            //decrease
            $comment = $reply->getComment();
            if ($comment) {
                $subject = $comment->getSubject();
                if ($subject) {
                    $count = $subject->getReplyCount();
                    $subject->update([
                        'reply_count' => $count - 1
                    ]);
                }
                
                $count = $comment->getReplyCount();
                $comment->update([
                    'reply_count' => $count - 1
                ]);
            }
        });

        static::created(function ($reply) {
            $comment = $reply->getComment();
            $subject = $comment->getSubject();
        });
    }

    public function getItems()
    {
        if (in_array($this->type, ['text'])) {
            return [];
        }
        if (! $this->items) {
            $items = CommentReplyItem::findByField('reply_id', $this->id, true);
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
