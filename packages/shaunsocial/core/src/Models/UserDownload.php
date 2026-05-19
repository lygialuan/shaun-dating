<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Enum\UserDownloadStatus;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\HasUser;

class UserDownload extends Model
{
    use HasCacheQueryFields, HasStorageFiles, HasUser;

    protected $storageFields = [
        'file_id',
    ];
    
    protected $cacheQueryFields = [
        'user_id'
    ];

    protected $fillable = [
        'user_id',
        'file_id',
        'status',
        'params'
    ];

    protected $casts = [
        'status' => UserDownloadStatus::class
    ];

    public function canDownload()
    {
        return $this->status == UserDownloadStatus::DONE;
    }

    public function getDownloadLink()
    {
        if ($this->file_id) {
            return $this->getFile('file_id')->getUrl();
        }

        return '';
    }

    public function getParams()
    {
        return $this->params ? json_decode($this->params, true) : [];
    }
}
