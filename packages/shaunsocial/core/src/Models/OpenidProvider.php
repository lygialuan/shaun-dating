<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;

class OpenidProvider extends Model
{
    use HasStorageFiles, HasCacheQueryFields;

    protected $storageFields = [
        'photo_id'
    ];

    protected $cacheQueryFields = [
        'id',
        'app_name'
    ];
    
    protected $fillable = [
        'name',
        'app_name',
        'photo_id',
        'photo_default',
        'server',
        'client_id',
        'client_secret',
        'scope',
        'authorize_endpoint',
        'access_token_endpoint',
        'get_user_info_endpoint',
        'is_active',
        'user_id_map',
        'email_map',
        'name_map',
        'avatar_map',
        'is_core',
        'order'
    ];

    public function getPhoto()
    {
        if ($this->photo_id) {
            $file = StorageFile::findByField('id', $this->photo_id);
            if ($file) {
                return $file->getUrl();
            }
        } elseif ($this->photo_default) {
            return asset($this->photo_default);
        }

        return null;
    }

    public function getHref()
    {
        return route('web.openid.auth',[
            'name' => $this->app_name
        ]);
    }

    public function getHrefApp()
    {
        return route('web.openid.auth',[
            'name' => $this->app_name,
            'app' => 1
        ]);
    }

    public function canDelete()
    {
        return ! $this->is_core;
    }

    static public function clearCache()
    {
        Cache::forget('openid_providers');
    }

    static public function getAll()
    {
        return Cache::rememberForever('openid_providers', function () {
            return self::where('is_active', true)->orderBy('order')->orderBy('id', 'DESC')->get();
        });
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($provider) {
            OpenidProviderUser::where('provider_id', $provider->id)->delete();
            self::clearCache();
        });

        static::creating(function ($provider) {
            self::clearCache();
        });

        static::updated(function ($provider) {
            self::clearCache();
        });
    }
}
