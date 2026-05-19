<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasMention;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;

class History extends Model
{
    use HasSubject, HasMention, HasCachePagination, HasUser;
    protected $mentionField = 'content';

    public function getListCachePagination()
    {
        return [
            'history_'.$this->subject_type.'_'.$this->subject_id
        ];
    }
    
    protected $fillable = [
        'user_id',
        'content',
        'is_first'
    ];

    protected $casts = [
        'is_first' => 'boolean',
    ];


    public function getContent($viewer)
    {
        if ($this->content) {
            return $this->getMentionContent($viewer);
        }

        if ($this->is_first) {
            $subject = $this->getSubject();
            if ($subject) {
                return $subject->getHistoryContentFirst();
            }
        }

        return '';
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($history) {
            if (! self::where('subject_type', $history->subject_type)->where('subject_id', $history->subject_id)->count()) {
                $history->is_first = true;
            }
        });
    }
}
