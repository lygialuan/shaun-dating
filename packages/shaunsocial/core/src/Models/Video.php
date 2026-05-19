<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Http\Resources\Utility\VideoResource;
use Packages\ShaunSocial\Core\Support\Facades\Utility;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\IsSubject;

class Video extends Model
{
    use HasCacheQueryFields, HasStorageFiles, IsSubject;
    
    protected $cacheQueryFields = [
        'id',
    ];

    protected $storageFields = [
        'thumb_file_id',
        'file_id'
    ];

    protected $fillable = [
        'user_id',
        'thumb_file_id',
        'file_id',
        'is_converted',
        'duration'
    ];

    protected $casts = [
        'is_converted' => 'boolean',
    ];

    public static function getResourceClass()
    {
        return VideoResource::class;
    }

    public function runQueue()
    {
        return Utility::convertVideoFromVideoModel($this);
    }

    public function getOgImage()
    {
        $file = $this->getFile('thumb_file_id');
        if ($file) {
            return $file->getOgImage();
        } else {
            return '';
        }
        
    }
}
