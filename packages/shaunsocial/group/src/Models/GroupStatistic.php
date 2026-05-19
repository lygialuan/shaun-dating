<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasSubject;

class GroupStatistic extends Model
{
    use HasSubject;

    protected $fillable = [
        'group_id',
        'type',
        'user_id',
        'hash'
    ];

    static public function getHash($groupId, $type, $viewerId, $subject = null)
    {
        $key = $groupId.$type.$viewerId;
        if ($subject) {
            $key .= $subject->getSubjectType().$subject->id;
        }
        return md5($key);
    }

    static public function add($groupId, $type, $viewer, $subject = null ,$hasLock = true)
    {
        $viewerId = $viewer ? $viewer->id : 0;
        $hash = self::getHash($groupId, $type, $viewerId, $subject);
        
        $data = [
            'group_id' => $groupId,
            'type' => $type,
            'user_id' => $viewerId,
            'hash' => $hash
        ];

        if ($subject) {
            $data['subject_type'] = $subject->getSubjectType();
            $data['subject_id'] = $subject->id;
        }
        if ($hasLock) {
            $key = 'group_statistic_lock_'.$groupId.$type;
            if ($subject) {
                $key .= $subject->getSubjectType().$subject->id;
            } 
            if ($viewer) {
                $key.=$viewer->id;
            } else {
                $key.=request()->ip();
            }
            $lock = Cache::lock($key, config('shaun_user_page.statistic_lock'));
            if ($lock->get()) {
                self::create($data);
            }
        } else {
            self::create($data);
        }
    }

    static public function remove($groupId, $type, $viewerId, $subject = null)
    {
        $hash = self::getHash($groupId, $type, $viewerId, $subject);
        $statistic = self::where('hash', $hash);
        if ($statistic) {
            $statistic->delete();
        }
    }

    protected static function booted()
    {
        parent::booted();
    
        static::created(function ($statistic) {
            if (in_array($statistic->type, ['member'])) {
                $member = GroupMember::getMember($statistic->user_id, $statistic->group_id);
                if ($member && $member->last_active->diffInSeconds(now()) > config('shaun_group.last_active_update')) {
                    $member->update([
                        'last_active' => now()
                    ]);
                }
            }
        });
    }

}
