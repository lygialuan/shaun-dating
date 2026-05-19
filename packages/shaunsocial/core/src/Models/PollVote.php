<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasUser;

class PollVote extends Model
{
    use HasCachePagination, HasUser;

    protected $fillable = [
        'user_id',
        'poll_id',
        'poll_item_id'
    ];

    public function getListCachePagination()
    {
        return [
            'poll_item_votes_'.$this->poll_item_id
        ];
    }

    public static function getVotes($pollId, $userId)
    {
        return Cache::remember(self::getCacheVoteKey($pollId, $userId), config('shaun_core.cache.time.model_query'), function () use ($pollId, $userId) {
            return self::where('user_id', $userId)->where('poll_id', $pollId)->get();
        });
    }

    public static function getVote($pollId, $pollItemId, $userId)
    {
        if (!$userId){
    		return false;
        }
        
        $votes = self::getVotes($pollId, $userId);
        
        return $votes->first(function ($value, $key) use ($pollItemId) {
            return $value->poll_item_id  == $pollItemId;
        });
    }

    public function getPoll()
    {
        return Poll::findByField('id', $this->poll_id);
    }

    public function getPollItem()
    {
        return PollItem::findByField('id', $this->poll_item_id);
    }

    public static function getCacheVoteKey($pollId, $userId)
    {
        return 'poll_vote_'.$pollId.'_'.$userId;
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($pollVote) {
            Cache::forget(self::getCacheVoteKey($pollVote->poll_id, $pollVote->user_id));
            //increase
            $poll = $pollVote->getPoll();
            $count = $poll->getVoteCount();
            $poll->update([
                'vote_count' => $count + 1
            ]);
            
            $pollItem = $pollVote->getPollItem();
            $count = $pollItem->getVoteCount();
            $pollItem->update([
                'vote_count' => $count + 1
            ]);
        });

        static::deleting(function ($pollVote) {
            Cache::forget(self::getCacheVoteKey($pollVote->poll_id, $pollVote->user_id));
            //decrease
            $poll = $pollVote->getPoll();
            $count = $poll->getVoteCount();
            
            $poll->update([
                'vote_count' => $count - 1
            ]);
            
            $pollItem = $pollVote->getPollItem();
            $count = $pollItem->getVoteCount();
            
            $pollItem->update([
                'vote_count' => $count - 1
            ]);
        });
    }
}
