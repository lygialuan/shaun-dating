<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class UserDelete extends Model
{
    protected $fillable = [
        'user_id',
        'is_page'
    ];

    protected $casts = [
        'is_page' => 'boolean',
    ];
}
