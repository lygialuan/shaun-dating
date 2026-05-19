<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Illuminate\Support\Facades\Cache;

class PollItem extends Model
{
    use HasCacheQueryFields, HasCachePagination, HasUser;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'name',
        'vote_count',
        'user_id',
        'poll_id'
    ];

    protected $poll = null;
    protected $userVotes = [];

    public static function getItemsByPollId($pollId)
    {
        return Cache::remember(self::getCachePollKey($pollId), config('shaun_core.cache.time.model_query'), function () use ($pollId)  {
            return self::where('poll_id', $pollId)->orderBy('id','ASC')->get();
        });
    }

    public function getVote($viewerId){
        if (! isset($this->userVotes[$viewerId])) {
            $this->userVotes[$viewerId] = PollVote::getVote($this->poll_id, $this->id, $viewerId);
        }
        return $this->userVotes[$viewerId];
    }

    public function setVote($viewerId, $value)
    {
        $this->userVotes[$viewerId] = $value;
    }

    public function getPoll(){
        if (! $this->poll) {
            $this->poll = Poll::findByField('id', $this->poll_id);
        }
        return $this->poll;
    }

    public function setPoll($poll)
    {
        $this->poll = $poll;
    }

    public function getPollItemPercent(){
        $total = $this->getPoll()->vote_count;
        return $total ? round(($this->vote_count / $total) * 100) : 0;
    }

    public static function getCachePollKey($pollId)
    {
        return 'poll_item_'.$pollId;
    }

    public function clearCache()
    {
        Cache::forget(self::getCachePollKey($this->poll_id));
    }

    public function getVoteCount()
    {
        return PollVote::where('poll_item_id', $this->id)->count();
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($pollItem) {
            $pollItem->clearCache();
        });

        static::updated(function ($pollItem) {
            $pollItem->clearCache();
        });
    }
}
