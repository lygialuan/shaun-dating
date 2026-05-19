<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;

class Setting extends Model
{
    use HasStorageFiles;

    protected $storageFields = [
        'value' => [
            'type' => 'image',
        ],
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'value',
        'params',
        'type',
        'order',
        'group_id',
        'group_sub_id',
        'hidden'
    ];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    public $timestamps = false;

    public function getParams()
    {
        return json_decode($this->params, true);
    }

    public function getGroup()
    {
        return SettingGroup::findByField('id', $this->group_id);
    }

    public function getGroupSub()
    {
        return SettingGroupSub::findByField('id', $this->group_sub_id);
    }

    public static function booted()
    {
        parent::booted();

        static::saving(function ($setting) {
            if ($setting->key == 'site.url' && $setting->value != $setting->getOriginal('value')) {
                putPermanentEnv('APP_URL', $setting->value);
            }
        });
    }
}
