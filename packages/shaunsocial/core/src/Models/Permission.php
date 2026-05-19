<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class Permission extends Model
{
    use HasTranslations, HasCacheQueryFields;

    protected $fillable = [
        'name',
        'description',
        'key',
        'is_support_guest',
        'is_support_moderator',
        'group_id',
        'type',
        'order',
        'message_error',
        'has_message_error'
    ];

    protected $translatable = [
        'message_error',
    ];

    protected $cacheQueryFields = [
        'id',
        'key'
    ];

    protected $casts = [
        'is_support_guest' => 'boolean',
        'is_support_moderator' => 'boolean',
        'has_message_error' => 'boolean'
    ];

    public function getGroup()
    {
        return PermissionGroup::findByField('id', $this->group_id);
    }

    public function haveMessagePermission()
    {
        return !$this->is_support_moderator && $this->has_message_error;
    }

    public static function getMessageErrorByKey($key, $value = '')
    {
        $permission = Permission::findByField('key', $key);
        $messageDefault = __('You do not have permission.');
        if ($permission && $permission->haveMessagePermission()) {
            $message = $permission->getTranslatedAttributeValue('message_error');
            return ($message ? ($value ? str_replace('[x]', $value, $message) : $message) : $messageDefault);
        }

        return $messageDefault;
    }

    public function clearCacheTranslate()
    {
        $this->clearCacheQueryFields();
        Cache::forget('permissions_for_user');
    }

    static function getPermissionsForUser()
    {
        return Cache::rememberForever('permissions_for_user', function () {
            return self::where('is_support_moderator', false)->get();
        });
    }
}
