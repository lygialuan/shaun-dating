<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutBlock extends Model
{
    protected $fillable = [
        'component',
        'title',
        'package',
        'class',
        'support_header_footer',
        'enable'
    ];

    protected $casts = [
        'support_header_footer' => 'boolean',
        'enable' => 'boolean',
    ];
}
