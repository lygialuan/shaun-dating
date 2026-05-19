<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\Role;

trait HasPermissions
{
    public function initializeHasPermissions()
    {
        $this->fillable[] = 'role_access';
    }

    public function hasPermission($roleId)
    {
        $role = Role::findByField('id', $roleId);
        if ($role->isSuperAdmin()) {
            return true;
        }
        $roleAccess = $this->getRoleAccess();

        return in_array('all', $roleAccess) || in_array($roleId, $roleAccess);
    }

    public function getRoleAccess()
    {
        if (! $this->id) {
            return ['all'];
        }

        $roleAccess = json_decode($this->role_access, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $roleAccess = [];
        }

        return $roleAccess;
    }
}
