<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'name',
        'alias',
        'support_icon',
        'support_child'
    ];

    protected $casts = [
        'support_icon' => 'boolean',
        'support_child' => 'boolean',
    ];

    public static function getAll()
    {
        return Cache::rememberForever('menus', function () {
            return self::all();
        });
    }
}
