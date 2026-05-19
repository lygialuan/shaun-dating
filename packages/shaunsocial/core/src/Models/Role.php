<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;

class Role extends Model
{
    use HasCacheQueryFields, HasDeleted;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'name',
        'email',
        'is_moderator',
        'is_supper_admin',
        'is_default'
    ];

    protected $casts = [
        'is_moderator' => 'boolean',
        'is_supper_admin' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    public function getPermissionValues()
    {
        return Cache::rememberForever('permissions_role_'.$this->id, function () {
            return RolePermission::with('permission')->where('role_id', $this->id)->get()->pluck('value', 'permission.key');
        });
    }

    public function isModerator()
    {
        return $this->getAttribute('is_moderator');
    }

    public function isSuperAdmin()
    {
        return $this->getAttribute('is_supper_admin');
    }

    public function canDelete()
    {
        return ! in_array($this->id, config('shaun_core.role.id')) && $this->users()->count() == 0;
    }

    public function canPermission()
    {
        return ! in_array($this->id, [config('shaun_core.role.id.root'), config('shaun_core.role.id.guest')]);
    }

    public function canDefault()
    {
        return $this->isMember();
    }

    public function isMember()
    {
        return $this->id != config('shaun_core.role.id.guest') && ! $this->isSuperAdmin() && ! $this->isModerator();
    }

    public static function getMemberRoles()
    {
        $roles = self::all();
        return $roles->filter(function ($role, $key) {
            return $role->isMember();
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    static public function getDefault()
    {
        return Cache::rememberForever('role_default', function () {
            return self::where('is_default', true)->first();
        });
    }

    static public function getListEdit($viewer)
    {
        $roles = self::all();

        return $roles->filter(function ($value, $key) use ($viewer) {
            if ($value->id == config('shaun_core.role.id.guest')) {
                return false;
            }
            
            if ($viewer->isRoot()) {
                return true;
            }

            if ($viewer->isSuperAdmin()) {
                return ! $value->is_supper_admin;
            }

            if ($viewer->isModerator()) {
                return ! $value->is_supper_admin && ! $value->is_moderator;
            }

            return false;
        });
    }
}
