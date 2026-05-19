<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class PermissionGroup extends Model
{
    use HasCacheQueryFields;
    
    protected $cacheQueryFields = [
        'id',
    ];

    protected $_permissions = null;
    public function getPermissions($role)
    {
        $builder = Permission::where('group_id', $this->id)->orderBy('order', 'ASC');
        if ($role->id == config('shaun_core.role.id.guest')) {
            $builder->where('is_support_guest', true);
        }

        if (! $role->is_moderator) {
            $builder->where('is_support_moderator', false);
        } else {
            $builder->where('is_support_moderator', true);
        }

        return $builder->get();
    }
}
