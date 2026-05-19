<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserListMessageCron extends Model
{
    use HasUser;

    protected $fillable = [
        'user_id',
        'message_id'
    ];

    public function getUserListMessage()
    {
        return UserListMessage::findByField('id', $this->message_id);
    }
}
