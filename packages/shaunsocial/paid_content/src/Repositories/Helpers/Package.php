<?php


namespace Packages\ShaunSocial\PaidContent\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Traits\Utility;

class Package
{
    use Utility;

    public function install()
    {
        // add permission
        $roles = Role::getMemberRoles();
        $permissions = Permission::where('key', ['paid_content.allow_create'])->get();
        foreach ($permissions as $permission) {
            $permission->createTranslationsWithKey('en');

            foreach ($roles as $role) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'value' => 0
                ]);
            }
        }
    }

    public function afterSaveSetting($setting)
    {
        if ($setting->key == 'shaun_paid_content.enable') {
            $enable = $setting->value;
            PostHome::where('is_paid',true)->update([
                'show' => $enable
            ]);

            Post::where('is_paid',true)->update([
                'show' => $enable
            ]);
        }
    }
}
