<?php


namespace Packages\ShaunSocial\UserSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Packages\ShaunSocial\Core\Models\Role;

class UserSubscriptionPackage extends Model
{
    use HasCacheQueryFields, HasDeleted, HasTranslations;
    
    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'description'
    ];
    
    protected $fillable = [
        'name',
        'description',
        'role_id',
        'expire_role_id',
        'is_active',
        'is_show',
        'order',
        'is_delete',
        'is_highlight',
        'badge_name',
        'badge_background_color',
        'badge_text_color',
        'badge_border_color',
        'is_show_badge'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_show_badge' => 'boolean'
    ];

    static function getAll()
    {
        return Cache::rememberForever('user_subscription_packages', function () {
            return self::orderBy('order', 'ASC')->orderBy('id', 'DESC')->where('is_active', true)->where('is_show', true)->get();
        });
    }

    function checkShow()
    {
        return $this->is_show;
    }

    function getRole()
    {
        return Role::findByField('id', $this->role_id);
    }

    public function getRoleDowngrade()
    {
        return Role::findByField('id', $this->expire_role_id);
    }

    public function getPlans()
    {
        return UserSubscriptionPlan::getPlansByPackageId($this->id);
    }

    public function clearCache()
    {
        Cache::forget('user_subscription_packages');
    }

    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    protected static function booted()
    {
        parent::booted();

        static::saved(function ($package) {
            $package->clearCache();
        });
    }
}
