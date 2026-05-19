<?php

namespace Packages\ShaunSocial\UserPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasSubject;

class UserPageStatistic extends Model
{
    use HasSubject;

    protected $fillable = [
        'user_page_id',
        'type',
        'user_id',
        'hash'
    ];

    static public function getHash($pageId, $type, $viewerId, $subject = null)
    {
        $key = $pageId.$type.$viewerId;
        if ($subject) {
            $key .= $subject->getSubjectType().$subject->id;
        }
        return md5($key);
    }

    static public function add($pageId, $type, $viewer, $subject = null ,$hasLock = true)
    {
        $viewerId = $viewer ? $viewer->id : 0;
        $hash = self::getHash($pageId, $type, $viewerId, $subject);
        
        $data = [
            'user_page_id' => $pageId,
            'type' => $type,
            'user_id' => $viewerId,
            'hash' => $hash
        ];

        if ($subject) {
            $data['subject_type'] = $subject->getSubjectType();
            $data['subject_id'] = $subject->id;
        }
        if ($hasLock) {
            $key = 'user_page_statistic_lock_'.$pageId.$type;
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

    static public function remove($pageId, $type, $viewerId, $subject = null)
    {
        $hash = self::getHash($pageId, $type, $viewerId, $subject);
        $statistic = self::where('hash', $hash);
        if ($statistic) {
            $statistic->delete();
        }
    }
}
