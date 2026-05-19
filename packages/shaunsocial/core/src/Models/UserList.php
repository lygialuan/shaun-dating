<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserList extends Model
{
    use HasCacheQueryFields, HasCachePagination, HasUser;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'member_count'
    ];

    public function getListCachePagination()
    {
        return [
            'user_list_'.$this->user_id,
            'user_list_can_send_'.$this->user_id
        ];
    }

    public function getListFieldPagination()
    {
        return [
            'name',
            'member_count'
        ];
    }

    public function getMemberCount()
    {
        return UserListMember::where('user_list_id', $this->id)->count();
    }

    public function canSend($viewerId)
    {
        return $this->isOwner($viewerId) && $this->member_count;
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($list) {
            $list->member_count = 0;
        });

        static::deleted(function ($list) {
            UserListMember::where('user_list_id', $list->id)->delete() ;
        });
    }
}
