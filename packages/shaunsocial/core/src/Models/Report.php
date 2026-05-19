<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class Report extends Model
{
    use HasSubject, HasUser, HasCacheQueryFields;

    protected $fillable = [
        'user_id',
        'to_user_id',
        'category_id',
        'reason',
    ];

    protected $cacheQueryFields = [
        'id'
    ];

    public function getCategory()
    {
        return ReportCategory::findByField('id', $this->category_id);
    }

    public static function getReport($userId, $subjectType, $subjectId)
    {
        return Cache::remember(self::getKeyCache($userId, $subjectType, $subjectId), config('shaun_core.cache.time.model_query'), function () use ($userId, $subjectType, $subjectId) {
            $report = self::where('user_id', $userId)->where('subject_type', $subjectType)->where('subject_id', $subjectId)->first();

            return is_null($report) ? false : $report;
        });
    }

    public static function getKeyCache($userId, $subjectType, $subjectId)
    {
        return 'report_'.$userId.'_'.$subjectType.'_'.$subjectId;
    }

    public function checkExits()
    {
        if (! $this->user_id) {
            return $this->getSubject();
        }
        return $this->getUser() && $this->getSubject();
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($report) {
            Cache::forget(self::getKeyCache($report->user_id, $report->subject_type, $report->subject_id));
        });

        static::deleted(function ($report) {
            Cache::forget(self::getKeyCache($report->user_id, $report->subject_type, $report->subject_id));
        });
    }
}
