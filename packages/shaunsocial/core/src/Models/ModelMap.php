<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ModelMap extends Model
{
    protected $fillable = [
        'subject_type',
        'model_class',
    ];

    public static function getAll()
    {
        return Cache::rememberForever('model_maps', function () {
            return self::all();
        });
    }

    public static function getModel($subjectType)
    {
        $modelMaps = self::getAll();
        $modelMap = $modelMaps->first(function ($value, $key) use ($subjectType) {
            return $value->subject_type == $subjectType;
        });

        return $modelMap ? $modelMap->model_class : '';
    }
}
