<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Http\Resources\Utility\AudioResource;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class Audio extends Model
{
    use HasCacheQueryFields, HasStorageFiles, IsSubject;
    
    protected $table = 'audios';

    protected $cacheQueryFields = [
        'id',
    ];

    protected $storageFields = [
        'file_id'
    ];

    protected $fillable = [
        'file_id',
        'duration'
    ];

    public static function getResourceClass()
    {
        return AudioResource::class;
    }
}
