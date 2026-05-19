<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LayoutMap extends Model
{
    protected $fillable = [
        'router',
        'layout_class',
    ];

    public static function getAll()
    {
        return Cache::rememberForever('layout_maps', function () {
            return self::all();
        });
    }

    public static function getLayout($router)
    {
        $layoutMaps = self::getAll();
        $layoutMap = $layoutMaps->first(function ($value, $key) use ($router) {
            return $value->router == $router;
        });

        return $layoutMap ? $layoutMap->layout_class : '';
    }
}
