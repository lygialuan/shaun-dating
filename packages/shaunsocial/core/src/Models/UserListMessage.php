<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Enum\UserListMessageStatus;
use Packages\ShaunSocial\Core\Enum\UserListMessageType;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserListMessage extends Model
{
    use HasUser, HasCacheQueryFields, HasSubject;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'list_id',
        'current',
        'content',
        'status'
    ];

    protected $casts = [
        'type' => UserListMessageType::class,
        'status' => UserListMessageStatus::class
    ];
}
