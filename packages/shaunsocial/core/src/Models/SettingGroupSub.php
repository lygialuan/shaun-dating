<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class SettingGroupSub extends Model
{    
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
    ];
    
    public $timestamps = false;

    protected $fillable = [
        'key',
        'name',
        'group_id',
        'order',
        'package'
    ];
    
    public function settings()
    {
        return $this->hasMany(Setting::class, 'group_sub_id')->where('hidden', false)->orderBy('order');
    }
}
