<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserFollowNotificationCron extends Model
{
    use HasUser, HasSubject;
    
    protected $fillable = [
        'user_id',
        'class',
        'current',
        'package'
    ];
}
