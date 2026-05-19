<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Http\Resources\Utility\LinkResource;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class Link extends Model
{
    use HasCacheQueryFields, HasStorageFiles, IsSubject;

    protected $storageFields = [
        'photo_file_id',
    ];

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'photo_file_id',
        'youtube_id',
        'tiktok_id'
    ];

    public static function getResourceClass()
    {
        return LinkResource::class;
    }

    public function getOgImage()
    {
        $photo = $this->getFile('photo_file_id');
        return $photo ? $photo->getUrl() : '';
    }
}
