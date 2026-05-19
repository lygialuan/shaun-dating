<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Http\Resources\Post\PollResource;

class Poll extends Model
{
    use HasCacheQueryFields, IsSubject, HasCachePagination, HasUser;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'vote_count',
        'user_id',
        'post_id',
        'close_minute'
    ];

    protected $items = null;

    public static function getResourceClass()
    {
        return PollResource::class;
    }

    public function getParams()
    {
        return json_decode($this->params, true);
    }
    
    public function getItems()
    {
        if (! $this->items) {
            $this->items = PollItem::getItemsByPollId($this->id);
        };

        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getPost()
    {
        return Post::findByField('id', $this->post_id);
    }

    public function getVoteCount()
    {
        return PollVote::where('poll_id', $this->id)->count();
    }

    public function isClosed()
    {
        $time = strtotime($this->created_at . ' + ' .$this->close_minute .' minutes');
		return (time() > $time);
    }

    public function canVote($viewerId)
    {
        if (! $viewerId) {
            return true;
        }
        return !$this->isClosed();
    }

    public function getLeftTime()
    {
        $time = strtotime($this->created_at . ' + ' .$this->close_minute .' minutes');
        $leftTime = $time - time();
        if ($leftTime > 0) {
            $day = $leftTime / (24*60*60);
            if ($day > 1) {
                $day = floor($day);
                return $day. ' '. ($day > 1 ? __('days left') : __('day left'));
            }

            $hour = $leftTime/ (60*60);
            if ($hour > 1) {
                $hour = floor($hour);
                return $hour. ' '. ($hour > 1 ? __('hours left') : __('hour left'));
            }

            $minute = $leftTime/60;
            if ($minute > 1) {
                $minute = floor($minute);
                return $minute. ' '. ($minute > 1 ? __('minutes left') : __('minute left'));
            }

            return $leftTime. ' '. ($leftTime > 1 ? __('seconds left') : __('second left'));
        }
        return '';
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($poll) {
            $poll->vote_count = 0;
        });

        static::deleted(function ($poll) {
            PollItem::where('poll_id', $poll->id)->delete();
            PollVote::where('poll_id', $poll->id)->delete();
        });
    }
}
