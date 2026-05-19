<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'permission_id',
        'value',
    ];

    public $timestamps = false;

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
