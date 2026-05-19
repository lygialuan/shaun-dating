<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'table_name',
        'column_name',
        'foreign_key',
        'locale',
        'value',
    ];

    public $timestamps = false;
}
