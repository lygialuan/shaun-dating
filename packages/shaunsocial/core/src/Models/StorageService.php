<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StorageService extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'key',
        'class',
        'config',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public $timestamps = false;

    public static function getAll()
    {
        return Cache::rememberForever('storage_services', function () {
            return self::all();
        });
    }

    public function getConfig()
    {
        return $this->config ? json_decode($this->config, true) : [];
    }

    public function getCountFile()
    {
        return StorageFile::where('service_key', $this->key)->count();
    }

    public function getTotalSize()
    {
        return StorageFile::where('service_key', $this->key)->sum('size');
    }

    public function getExtra()
    {
        return $this->extra ? json_decode($this->extra, true) : [];
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($service) {
            Cache::forget('storage_services');
        });

        static::saved(function ($service) {
            Cache::forget('storage_services');
        });
    }
}
