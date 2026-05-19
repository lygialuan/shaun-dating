<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class Key extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'name',
    ];

    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];

    public static function getValue($name, $default = '')
    {
        $key = self::findByField('name', $name);

        return $key ? $key->value : $default;
    }

    public static function setValue($name, $value)
    {
        $key = self::findByField('name', $name);
        if ($key) {
            $key->update([
                'value' => $value,
            ]);
        } else {
            self::create([
                'name' => $name,
                'value' => $value,
            ]);
        }
    }

    public static function removeKey($name)
    {
        $key = self::findByField('name', $name);
        if ($key) {
            $key->delete();
        }
    }
}
