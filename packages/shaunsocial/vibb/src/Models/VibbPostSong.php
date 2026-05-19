<?php


namespace Packages\ShaunSocial\Vibb\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Vibb\Http\Resources\VibbPostSongResource;

class VibbPostSong extends Model
{
    use HasCacheQueryFields, IsSubject;

    protected $cacheQueryFields = [
        'id'
    ];
    
    protected $fillable = [
        'name',
        'song_id',
        'post_id'
    ];

    public function getTitle()
    {
        return $this->name;
    }

    public static function getResourceClass()
    {
        return VibbPostSongResource::class;
    }

}
