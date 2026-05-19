<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasUser;

class GroupCron extends Model
{
    use HasUser;

    protected $fillable = [ // define saved data when use create($data) and update($data)
        'group_id',
        'user_id',
        'item_id',
        'params',
        'type',
        'current'
    ];
}
