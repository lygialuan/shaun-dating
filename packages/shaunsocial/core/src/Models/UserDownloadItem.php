<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

enum UserDownloadItemStatus: string {
    case RUNNING = 'running';
    case DONE = 'done';
}

class UserDownloadItem extends Model
{
    use HasCacheQueryFields;
    
    protected $cacheQueryFields = [
        'id'
    ];

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'params',
        'id_min',
        'parent_id',
        'package'
    ];

    protected $casts = [
        'status' => UserDownloadItemStatus::class
    ];

    public function getParams()
    {
        return $this->params ? json_decode($this->params, true) : [];
    }
}
