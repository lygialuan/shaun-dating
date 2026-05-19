<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserNotification extends Model
{
    use HasCachePagination, HasCacheQueryFields, HasSubject, HasUser, Prunable;
    
    protected $classSubject = null;
    protected $from = null;

    protected $fillable = [
        'user_id',
        'from_id',
        'hash',
        'class',
        'is_seen',
        'params',
        'is_viewed',
        'package',
        'is_system'
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'is_viewed' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected $cacheQueryFields = [
        'id',
        'hash'
    ];

    public function getListCachePagination()
    {
        return [
            'user_'.$this->user_id,
        ];
    }

    public function getListFieldPagination()
    {
        return [
            'is_seen'
        ];
    }

    static public function getSameHash($hashString)
    {
        return self::where('hash', $hashString)->orderBy('id', 'DESC')->first();
    }

    public function getClassNotification()
    {
        if (! $this->classSubject) {
            $class = $this->class;
            $this->classSubject = new $class($this);
        }

        return $this->classSubject;
    }

    public function getFrom()
    {
        return User::findByField('id', $this->from_id);
    }

    public function getParams()
    {
        return json_decode($this->params,true);
    }

    public function getCountHash()
    {
        if ($this->getClassNotification()->hasGroup()) {
            return Cache::remember('notification_hash_'.$this->hash, config('shaun_core.cache.time.model_query'), function () {
                return self::where('hash', $this->hash)->count(DB::raw('DISTINCT from_id'));
            });
        } else {
            return 1;
        }
    }

    public static function deleteFromAndSubject($from, $subject, $class)
    {
        $builder = self::where('from_id', $from->id)->where('class', $class);
        if ($subject) {
            $builder->where('subject_type', $subject->getSubjectType())->where('subject_id', $subject->id);
        }
        
        $builder->get()->each(function($notification){
            $notification->delete();
        });
    }

    public function checkExists()
    {        
        if ($this->subject_type) {
            $subject = $this->getSubject();
            if (! $subject) {
                return false;
            }
        }
        
        return $this->getClassNotification()->checkExists();
    }

    public function clearCache()
    {
        Cache::forget('notification_hash_'.$this->hash);
    }

    public function getHref()
    {
        $href = $this->getClassNotification()->getHref();
        $checkHref = strpos($href, '?') === FALSE;
        return $this->getClassNotification()->getHref().($checkHref ? '?' : '&').'notify_id='.$this->id;
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($notification) {
            $notification->clearCache();
        });

        static::deleted(function ($notification) {
            $notification->clearCache();

            //update notify count
            if (! $notification->is_viewed) {
                $notificationLast = self::where('hash', $notification->hash)->where('id', '!=', $notification->id)->orderBy('id', 'DESC')->first();
                if (!$notificationLast || $notification->is_viewed) {
                    $user = User::findByField('id', $notification->user_id);
                    if ($user && $user->notify_count) {
                        $user->decrement('notify_count');
                    }                    
                }
            }            
        });
    }

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(setting('feature.notify_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
